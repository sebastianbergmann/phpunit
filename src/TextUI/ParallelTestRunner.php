<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI;

use function assert;
use function class_exists;
use function explode;
use function get_parent_class;
use function gettype;
use function is_array;
use function is_object;
use function is_resource;
use function is_subclass_of;
use function mt_srand;
use function serialize;
use function spl_object_id;
use function usleep;
use PHPUnit\Event;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\IterativeTestSuite;
use PHPUnit\Framework\PhptIterativeTestSuite;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestRunner\ChildProcessResultProcessor;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Metadata\Api\Dependencies;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\Exception as PhptException;
use PHPUnit\Runner\Parallel\CompletedWorkUnit;
use PHPUnit\Runner\Parallel\PersistentWorker;
use PHPUnit\Runner\Parallel\PhptRunner;
use PHPUnit\Runner\Parallel\PhptWorkUnit;
use PHPUnit\Runner\Parallel\ProcessBudget;
use PHPUnit\Runner\Parallel\ResultAggregator;
use PHPUnit\Runner\Parallel\Scheduler;
use PHPUnit\Runner\Parallel\TestClassWorkUnit;
use PHPUnit\Runner\Parallel\WorkerException;
use PHPUnit\Runner\Parallel\WorkerPool;
use PHPUnit\Runner\Parallel\WorkUnit;
use PHPUnit\Runner\Phpt\Parser;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use PHPUnit\Runner\ResultCache\ResultCache;
use PHPUnit\Runner\TestSuiteSorter;
use PHPUnit\TestRunner\TestResult\Facade as TestResultFacade;
use PHPUnit\TestRunner\TestResult\PassedTests;
use PHPUnit\TextUI\Configuration\Configuration;
use PHPUnit\Util\PHP\JobRunner;
use Throwable;

/**
 * Runs a test suite by distributing its test classes across a pool of worker
 * processes that execute them concurrently.
 *
 * The distribution unit is one test class: all of the selected tests of a class
 * are run together by a single worker, which preserves the class' shared
 * fixtures (#[BeforeClass] / #[AfterClass]) and its intra-class ordering.
 *
 * The top-level <testsuite> elements of an XML configuration are run one after
 * another, just as they are in sequential mode: only tests that belong to the
 * same top-level test suite run concurrently with each other.
 *
 * Apart from the parallel execution itself, this runner is a drop-in
 * replacement for the sequential TestRunner: it performs the same test suite
 * sorting and filtering and emits the same test runner lifecycle events, so the
 * parent process' output, logging, and result subsystem is unaffected.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ParallelTestRunner
{
    /**
     * How long to sleep, in microseconds, when a polling round finds that
     * neither the worker pool nor the PHPT runner has progressed, so that
     * waiting does not spin the CPU.
     */
    private const int POLL_INTERVAL_MICROSECONDS = 1000;

    /**
     * @throws RuntimeException
     */
    public function run(Configuration $configuration, ResultCache $resultCache, TestSuite $suite): void
    {
        try {
            Event\Facade::emitter()->testRunnerStarted();

            if ($configuration->executionOrder() === TestSuiteSorter::ORDER_RANDOMIZED) {
                mt_srand($configuration->randomOrderSeed());
            }

            // The durations recorded by a previous run inform both the
            // optional reordering of the suite and the scheduling of the
            // units across the workers.
            $resultCache->load();

            if ($configuration->executionOrder() !== TestSuiteSorter::ORDER_DEFAULT ||
                $configuration->executionOrderDefects() !== TestSuiteSorter::ORDER_DEFAULT ||
                $configuration->resolveDependencies()) {
                new TestSuiteSorter($resultCache)->reorderTestsInSuite(
                    $suite,
                    $configuration->executionOrder(),
                    $configuration->resolveDependencies(),
                    $configuration->executionOrderDefects(),
                );

                Event\Facade::emitter()->testSuiteSorted(
                    $configuration->executionOrder(),
                    $configuration->executionOrderDefects(),
                    $configuration->resolveDependencies(),
                );
            }

            (new TestSuiteFilterProcessor)->process($configuration, $suite);

            Event\Facade::emitter()->testRunnerExecutionStarted(
                Event\TestSuite\TestSuiteBuilder::from($suite),
            );

            $chunks = $this->collectChunks($configuration, $suite);

            if ($chunks !== []) {
                $this->execute($configuration, $resultCache, $suite, $chunks);
            }

            Event\Facade::emitter()->testRunnerExecutionFinished();
            Event\Facade::emitter()->testRunnerFinished();
            // @codeCoverageIgnoreStart
        } catch (Throwable $t) {
            throw new RuntimeException(
                $t->getMessage(),
                (int) $t->getCode(),
                $t,
            );
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * The chunks are run one after another; within a chunk, three kinds of
     * units run concurrently.
     *
     * Units whose tests may run in a worker process — those that are neither
     * attributed with #[DoNotRunInParallel] nor carry test data that cannot be
     * serialized for transport to a worker — are distributed across the worker
     * pool.
     *
     * PHPT tests are not PHPUnit\Framework\TestCase instances and cannot run in
     * a worker, so they run concurrently in the main process, each as its own
     * child process, honouring the conflict keys of their --CONFLICTS-- section.
     * The worker pool and the PHPT runner are advanced side by side in one
     * polling loop, so that neither has to wait for the other and results reach
     * the output the moment suite order allows.
     *
     * The remaining units run one at a time in the main PHPUnit process, each
     * at the moment its suite index comes up in the aggregator's release
     * sequence. A unit runs there when it is attributed with
     * #[DoNotRunInParallel] (the author has declared that its tests must not run
     * alongside others, for instance because they share a process-global
     * resource), when it is configured to run in a separate process (an
     * isolation that a shared worker cannot provide but the main process can),
     * when one of its tests depends on a test of another class (whose result
     * is only available in the main process, once the unit it belongs to has
     * been released), or when its test data cannot be serialized for transport
     * to a worker (in which case the main process is the only place it can run
     * at all). Running
     * in the main process is ordinary execution that behaves exactly as it would
     * in sequential mode. Any remaining standalone test — one that is neither a
     * TestCase nor a PHPT test — is run the same way, at its own suite index.
     *
     * In sequential mode, TestSuite::run() wraps every suite's tests in a pair
     * of "test suite started" / "test suite finished" events. The workers and
     * the in-process units produce these for the test classes they run, but
     * nothing produces them for the root suite and the top-level test suites
     * in a parallel run; they are therefore emitted here, around the chunks.
     * The loggers that reconstruct the suite hierarchy from these events —
     * JUnit XML, Open Test Reporting, TeamCity — depend on them.
     *
     * Within a chunk, the worker units and the PHPT tests are dispatched in
     * the order of their recorded durations, longest first, so that the
     * longest-running work does not become the straggler that the workers
     * wait for at the end of the chunk (see Scheduler). The results are
     * released in suite order regardless of the dispatch order.
     *
     * @param non-empty-list<array{suite: TestSuite, units: list<WorkUnit>, phpt: list<PhptWorkUnit>, standalone: list<array{index: non-negative-int, test: Test}>}> $chunks
     *
     * @throws WorkerException
     */
    private function execute(Configuration $configuration, ResultCache $resultCache, TestSuite $suite, array $chunks): void
    {
        $scheduler = new Scheduler($resultCache);

        $aggregator = new ResultAggregator(
            Event\Facade::instance(),
            Event\Facade::emitter(),
            PassedTests::instance(),
            CodeCoverage::instance(),
            static function (): bool
            {
                return TestResultFacade::shouldStop();
            },
        );

        $processIsolation = $configuration->processIsolation();

        $runs         = [];
        $poolIsNeeded = false;
        $phptIsNeeded = false;

        foreach ($chunks as $chunk) {
            $parallel = [];

            foreach ($chunk['units'] as $unit) {
                if ($unit instanceof TestClassWorkUnit &&
                    ($processIsolation ||
                     $this->mustNotRunInParallel($unit) ||
                     $this->requiresProcessIsolation($unit) ||
                     $this->hasCrossClassDependencies($unit) ||
                     !$this->canBeSerialized($unit))) {
                    // The unit keeps its global suite index; the aggregator runs
                    // it in the main process at the moment that index comes up in
                    // the release sequence, which keeps the output in global
                    // suite order.
                    $aggregator->registerInProcessUnit(
                        $unit->index(),
                        function () use ($unit): void
                        {
                            $this->runInProcess($unit);
                        },
                    );

                    continue;
                }

                $parallel[] = $unit;
            }

            foreach ($chunk['standalone'] as $item) {
                $test = $item['test'];

                $aggregator->registerInProcessUnit(
                    $item['index'],
                    static function () use ($test): void
                    {
                        $test->run();
                    },
                );
            }

            if ($parallel !== []) {
                $poolIsNeeded = true;
            }

            if ($chunk['phpt'] !== []) {
                $phptIsNeeded = true;
            }

            $runs[] = [
                'suite'    => $chunk['suite'],
                'parallel' => $scheduler->schedule($parallel),
                'phpt'     => $scheduler->schedule($chunk['phpt']),
            ];
        }

        // The pool and the PHPT runner share one budget of concurrently
        // executing units, so that a chunk that contains both test classes
        // and PHPT tests never executes more units at once than the number
        // of parallel workers that was asked for.
        $budget = new ProcessBudget($configuration->numberOfParallelWorkers());

        // The pool and the PHPT runner are created once and reused across the
        // chunks, so that the worker processes are booted only once.
        $pool = null;

        if ($poolIsNeeded) {
            $pool = $this->createPool($configuration->numberOfParallelWorkers(), $budget);

            $pool->start();
        }

        $phptRunner = null;

        if ($phptIsNeeded) {
            $phptRunner = $this->createPhptRunner($configuration, $budget);
        }

        // When the suite was partitioned into chunks, the chunks are the
        // root suite's top-level test suites, and the root suite needs an
        // envelope of its own around all of them. When it was not, the only
        // chunk is the root suite itself, whose envelope is emitted by
        // runChunk().
        $rootSuiteValueObject = null;

        if ($chunks[0]['suite'] !== $suite) {
            $rootSuiteValueObject = Event\TestSuite\TestSuiteBuilder::from($suite);
        }

        if ($rootSuiteValueObject !== null) {
            Event\Facade::emitter()->testSuiteStarted($rootSuiteValueObject);
        }

        try {
            foreach ($runs as $run) {
                if ($this->runChunk($run['suite'], $run['parallel'], $run['phpt'], $pool, $phptRunner, $aggregator)) {
                    // The run was stopped early; the remaining chunks are
                    // abandoned, exactly as the sequential runner does not
                    // start another test suite once it has decided to stop.
                    break;
                }
            }
        } finally {
            if ($pool !== null) {
                $pool->stop();
            }
        }

        if ($rootSuiteValueObject !== null) {
            Event\Facade::emitter()->testSuiteFinished($rootSuiteValueObject);
        }
    }

    /**
     * Run the units of one chunk: the worker pool and the PHPT runner are
     * begun with the chunk's units and advanced side by side in one polling
     * loop, so that the chunk's test classes and PHPT tests execute
     * concurrently and their results and streamed events reach the parent the
     * moment they arrive. The in-process units interspersed among them run as
     * the release sequence reaches their indexes; the trailing flush releases
     * those that sit between this chunk and the next.
     *
     * The chunk's results are wrapped in the "test suite started" / "test
     * suite finished" envelope that its suite would have emitted had it been
     * run by TestSuite::run() in sequential mode.
     *
     * When the results forwarded so far call for the run to stop
     * (--stop-on-*), the chunk is aborted: the pumps drop their queued units
     * and terminate the units they are executing, and true is returned so
     * that the remaining chunks are abandoned. The results that were already
     * forwarded are exactly those a sequential run would have reported,
     * because the aggregator forwards in suite order and freezes as soon as
     * the stop condition holds.
     *
     * @param list<WorkUnit>     $parallel
     * @param list<PhptWorkUnit> $phpt
     */
    private function runChunk(TestSuite $suite, array $parallel, array $phpt, ?WorkerPool $pool, ?PhptRunner $phptRunner, ResultAggregator $aggregator): bool
    {
        $suiteValueObject = Event\TestSuite\TestSuiteBuilder::from($suite);

        // A chunk whose suite is itself a test class suite — a single test
        // class file given as the CLI argument — becomes one unit whose
        // execution emits that very envelope: the worker replays it, or the
        // in-process run emits it live. Emitting it here as well would nest
        // the class suite inside itself.
        $suiteEnvelopeIsEmittedByUnits = $suiteValueObject->isForTestClass();

        if (!$suiteEnvelopeIsEmittedByUnits) {
            Event\Facade::emitter()->testSuiteStarted($suiteValueObject);
        }

        // Run the in-process units that precede the chunk's first worker or
        // PHPT unit.
        $aggregator->flush();

        $activePool = null;

        if ($parallel !== []) {
            assert($pool !== null);

            $pool->begin(
                $parallel,
                static function (CompletedWorkUnit $completed) use ($aggregator): void
                {
                    $aggregator->add($completed);
                },
                static function (WorkUnit $unit, Event\EventCollection $events) use ($aggregator): void
                {
                    $aggregator->addStreamedEvents($unit->index(), $events);
                },
                static function (WorkUnit $unit) use ($aggregator): bool
                {
                    return $aggregator->discardStreamedEventsFor($unit->index());
                },
            );

            $activePool = $pool;
        }

        $activePhptRunner = null;

        if ($phpt !== []) {
            assert($phptRunner !== null);

            $phptRunner->begin(
                $phpt,
                static function (int $index, Event\EventCollection $events) use ($aggregator): void
                {
                    $aggregator->registerInProcessUnit(
                        $index,
                        static function () use ($events): void
                        {
                            Event\Facade::instance()->forward($events);
                        },
                    );

                    // Release everything that has become contiguous in suite
                    // order, so that progress is reported as the PHPT tests
                    // finish rather than buffered until the chunk is done.
                    $aggregator->flush();
                },
            );

            $activePhptRunner = $phptRunner;
        }

        $aborted = false;

        while (true) {
            // Stop early when the results forwarded so far call for it: the
            // queued units are dropped, and the units that are executing
            // right now are terminated without waiting for their results —
            // their results would be for tests that a sequential run would
            // not have run.
            if (TestResultFacade::shouldStop()) {
                if ($activePool !== null) {
                    $activePool->halt();
                }

                if ($activePhptRunner !== null) {
                    $activePhptRunner->halt();
                }

                $aborted = true;

                break;
            }

            $progressed = false;

            if ($activePool !== null && $activePool->tick()) {
                $progressed = true;
            }

            if ($activePhptRunner !== null && $activePhptRunner->tick()) {
                $progressed = true;
            }

            $poolIsFinished       = $activePool === null || $activePool->isFinished();
            $phptRunnerIsFinished = $activePhptRunner === null || $activePhptRunner->isFinished();

            if ($poolIsFinished && $phptRunnerIsFinished) {
                break;
            }

            // Neither the pool nor the PHPT runner progressed this round: sleep
            // briefly before polling again so that waiting does not spin the
            // CPU.
            if (!$progressed) {
                usleep(self::POLL_INTERVAL_MICROSECONDS);
            }
        }

        // Run the in-process units that follow the chunk's last unit. When
        // the run was stopped early, the aggregator is frozen and this
        // releases nothing.
        $aggregator->flush();

        // The chunk's very last releases may have tripped the stop condition
        // after the loop's final check; the remaining chunks are then
        // abandoned just the same.
        if (!$aborted && TestResultFacade::shouldStop()) {
            $aborted = true;
        }

        // The sequential runner emits this event when it decides to stop
        // between two tests; it is emitted here when the parallel runner
        // decides to abandon the run's remaining work.
        if ($aborted) {
            Event\Facade::emitter()->testRunnerExecutionAborted();
        }

        if (!$suiteEnvelopeIsEmittedByUnits) {
            Event\Facade::emitter()->testSuiteFinished($suiteValueObject);
        }

        return $aborted;
    }

    private function createPhptRunner(Configuration $configuration, ProcessBudget $budget): PhptRunner
    {
        $concurrency = $configuration->numberOfParallelWorkers();

        if (CodeCoverage::instance()->isActive()) {
            // The PHPT tests run in the main process and share its single code
            // coverage instance, so they must not collect coverage at the same
            // time; they are therefore run one at a time when coverage is on.
            $concurrency = 1;
        }

        $processor = new ChildProcessResultProcessor(
            Event\Facade::instance(),
            Event\Facade::emitter(),
            PassedTests::instance(),
            CodeCoverage::instance(),
        );

        return new PhptRunner(new JobRunner($processor), $concurrency, $budget);
    }

    /**
     * Run all of a unit's tests in the main PHPUnit process, exactly as the
     * sequential test runner would: the class is reassembled into a test suite
     * and run, which preserves its shared fixtures and intra-class ordering and
     * lets its events reach the parent's output and result subsystem directly.
     */
    private function runInProcess(TestClassWorkUnit $unit): void
    {
        $suite = TestSuite::empty($unit->className());

        foreach ($unit->tests() as $test) {
            $suite->addTest($test);
        }

        $suite->run();
    }

    /**
     * Whether every test of the unit carries data that survives serialization
     * for transport to a worker process. A unit that does not must be run in
     * the main process instead.
     *
     * Two kinds of data do not survive: data that cannot be serialized at all
     * (a closure, for example), which makes serialize() throw; and a resource,
     * which serialize() silently turns into the integer 0 rather than rejecting
     * — a test would then receive 0 in place of its resource and fail in a way
     * that has nothing to do with the code under test.
     */
    private function canBeSerialized(TestClassWorkUnit $unit): bool
    {
        foreach ($this->testCasesOf($unit) as $test) {
            try {
                serialize($test->providedData());
                serialize($test->dependencyInput());
            } catch (Throwable) {
                return false;
            }

            if ($this->containsResource($test->providedData()) ||
                $this->containsResource($test->dependencyInput())) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<object> $seen
     */
    private function containsResource(mixed $value, array &$seen = []): bool
    {
        if (is_resource($value)) {
            return true;
        }

        // is_resource() returns false for a resource that has already been
        // closed, yet serialize() degrades it to 0 just the same, so a closed
        // resource must be recognized here too.
        if (gettype($value) === 'resource (closed)') {
            return true;
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                if ($this->containsResource($item, $seen)) {
                    return true;
                }
            }

            return false;
        }

        if (is_object($value)) {
            $id = spl_object_id($value);

            if (isset($seen[$id])) {
                return false;
            }

            $seen[$id] = $value;

            foreach ((array) $value as $item) {
                if ($this->containsResource($item, $seen)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * A work unit is the whole of a test class, so a single test method that is
     * attributed with #[DoNotRunInParallel] excludes its entire class from the
     * parallel phase.
     */
    private function mustNotRunInParallel(TestClassWorkUnit $unit): bool
    {
        $className = $unit->className();

        $class = $className;

        do {
            if (MetadataRegistry::parser()->forClass($class)->isDoNotRunInParallel()->isNotEmpty()) {
                return true;
            }
        } while (($class = get_parent_class($class)) !== false);

        foreach ($this->testCasesOf($unit) as $test) {
            if (MetadataRegistry::parser()->forMethod($className, $test->name())->isDoNotRunInParallel()->isNotEmpty()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether any of the unit's tests depends on a test — or on all of the
     * tests — of another test class. Such a test needs the results of tests
     * that run in a different unit: possibly in a different worker, possibly
     * not finished yet, and in any case invisible to this unit's worker
     * process. The unit is therefore run in the main process, at its suite
     * index: by then, every unit that precedes it in suite order has been
     * released and the results its tests depend on have been imported —
     * exactly the state a sequential run would present to them.
     */
    private function hasCrossClassDependencies(TestClassWorkUnit $unit): bool
    {
        $className = $unit->className();

        foreach ($this->testCasesOf($unit) as $test) {
            foreach (Dependencies::dependencies($className, $test->name()) as $dependency) {
                // An invalid dependency — one whose declaration does not name
                // a test method — is not a cross-class dependency; the test
                // runner reports it wherever the unit runs.
                if ($dependency->isValid() && $dependency->getTargetClassName() !== $className) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Whether any of the unit's tests is configured to run in a separate
     * process. Such a test relies on process isolation that a shared worker
     * cannot provide; it is therefore run in the main process, where the test
     * runner spawns the isolated child process for it as usual.
     */
    private function requiresProcessIsolation(TestClassWorkUnit $unit): bool
    {
        $className = $unit->className();

        $class = $className;

        do {
            if (MetadataRegistry::parser()->forClass($class)->isRunTestsInSeparateProcesses()->isNotEmpty()) {
                return true;
            }
        } while (($class = get_parent_class($class)) !== false);

        foreach ($this->testCasesOf($unit) as $test) {
            if (MetadataRegistry::parser()->forMethod($className, $test->name())->isRunInSeparateProcess()->isNotEmpty()) {
                return true;
            }
        }

        return false;
    }

    /**
     * The test cases of a unit, with the test cases aggregated by a
     * DataProviderTestSuite or IterativeTestSuite member enumerated in its
     * place — including those of a retried or repeated data set, which sit
     * one level deeper, in an IterativeTestSuite inside a
     * DataProviderTestSuite.
     *
     * @return list<TestCase>
     */
    private function testCasesOf(TestClassWorkUnit $unit): array
    {
        $testCases = [];

        foreach ($unit->tests() as $test) {
            $this->appendTestCasesOf($test, $testCases);
        }

        return $testCases;
    }

    /**
     * @param list<TestCase> $testCases
     */
    private function appendTestCasesOf(Test $test, array &$testCases): void
    {
        if ($test instanceof TestCase) {
            $testCases[] = $test;

            return;
        }

        assert($test instanceof TestSuite);

        foreach ($test->tests() as $aggregated) {
            $this->appendTestCasesOf($aggregated, $testCases);
        }
    }

    /**
     * @param positive-int $numberOfWorkers
     */
    private function createPool(int $numberOfWorkers, ProcessBudget $budget): WorkerPool
    {
        $processor = new ChildProcessResultProcessor(
            Event\Facade::instance(),
            Event\Facade::emitter(),
            PassedTests::instance(),
            CodeCoverage::instance(),
        );

        $jobRunner = new JobRunner($processor);

        $workers = [new PersistentWorker($jobRunner, 0)];

        for ($id = 1; $id < $numberOfWorkers; $id++) {
            $workers[] = new PersistentWorker($jobRunner, $id);
        }

        return new WorkerPool($workers, $budget);
    }

    /**
     * Walk the suite and group the selected tests into units, partitioned into
     * chunks that are run one after another.
     *
     * A test suite that was assembled from an XML configuration runs its
     * top-level <testsuite> elements one after another in sequential mode; the
     * chunks preserve that boundary in parallel mode: each top-level test suite
     * becomes one chunk, and only the units of the same chunk run concurrently
     * with each other. A suite assembled from CLI arguments or a test-files
     * file has no such boundaries and forms a single chunk.
     *
     * All chunks draw their unit indexes from one shared sequence in suite
     * order, so that the aggregator releases the results of every chunk in
     * global suite order.
     *
     * Every chunk carries the suite it was collected from, so that the chunk's
     * results can be wrapped in that suite's "test suite started" / "test
     * suite finished" envelope. A chunk whose units were all filtered away is
     * dropped, mirroring how TestSuite::run() emits no envelope for a suite
     * that has become empty.
     *
     * @return list<array{suite: TestSuite, units: list<WorkUnit>, phpt: list<PhptWorkUnit>, standalone: list<array{index: non-negative-int, test: Test}>}>
     */
    private function collectChunks(Configuration $configuration, TestSuite $suite): array
    {
        $roots = [$suite];

        if (!$configuration->hasCliArguments() && !$configuration->hasTestFilesFile()) {
            $childSuites = [];

            foreach ($suite as $test) {
                if (!$test instanceof TestSuite) {
                    // A test directly under the root does not belong to any
                    // top-level test suite; there are no boundaries to honour.
                    // @codeCoverageIgnoreStart
                    $childSuites = [];

                    break;
                    // @codeCoverageIgnoreEnd
                }

                $childSuites[] = $test;
            }

            if ($childSuites !== []) {
                $roots = $childSuites;
            }
        }

        $index  = 0;
        $chunks = [];

        foreach ($roots as $root) {
            $chunk = $this->collectUnits($root, $index);

            if ($chunk['units'] === [] && $chunk['phpt'] === [] && $chunk['standalone'] === []) {
                continue;
            }

            $chunks[] = [
                'suite'      => $root,
                'units'      => $chunk['units'],
                'phpt'       => $chunk['phpt'],
                'standalone' => $chunk['standalone'],
            ];
        }

        return $chunks;
    }

    /**
     * Walk the suite and group the selected tests into units in suite order.
     *
     * The tests of a class are gathered into one TestClassWorkUnit, indexed by
     * the order in which the class first appears. A PHPT test becomes a
     * PhptWorkUnit of its own. Both kinds can run in a worker, so both are
     * returned as parallel-eligible units. Any other kind of test — one that is
     * neither a PHPUnit\Framework\TestCase nor a PHPT test — cannot be
     * reconstructed in a worker and is returned as a standalone test to run in
     * the main process. All draw from one shared index sequence so that they
     * can be released together in global suite order.
     *
     * @param non-negative-int $index
     *
     * @return array{units: list<WorkUnit>, phpt: list<PhptWorkUnit>, standalone: list<array{index: non-negative-int, test: Test}>}
     */
    private function collectUnits(TestSuite $suite, int &$index): array
    {
        /** @var array<class-string<TestCase>, array{index: non-negative-int, tests: list<DataProviderTestSuite|IterativeTestSuite|TestCase>}> $byClass */
        $byClass = [];

        /** @var list<array{index: non-negative-int, file: non-empty-string, conflicts: list<non-empty-string>}> $phpt */
        $phpt = [];

        /** @var list<array{index: non-negative-int, test: Test}> $standalone */
        $standalone = [];

        $this->collect($suite, $byClass, $phpt, $standalone, $index);

        $units = [];

        foreach ($byClass as $className => $group) {
            $units[] = new TestClassWorkUnit($group['index'], $className, $group['tests']);
        }

        $phptUnits = [];

        foreach ($phpt as $item) {
            $phptUnits[] = new PhptWorkUnit($item['index'], $item['file'], $item['conflicts']);
        }

        return [
            'units'      => $units,
            'phpt'       => $phptUnits,
            'standalone' => $standalone,
        ];
    }

    /**
     * @param array<class-string<TestCase>, array{index: non-negative-int, tests: list<DataProviderTestSuite|IterativeTestSuite|TestCase>}> $byClass
     * @param list<array{index: non-negative-int, file: non-empty-string, conflicts: list<non-empty-string>}>                               $phpt
     * @param list<array{index: non-negative-int, test: Test}>                                                                              $standalone
     * @param non-negative-int                                                                                                              $index
     */
    private function collect(TestSuite $suite, array &$byClass, array &$phpt, array &$standalone, int &$index): void
    {
        foreach ($suite as $test) {
            // The repetitions of a repeated PHPT test and the attempts of a
            // retried PHPT test are orchestrated by their suite's runTests()
            // method and must run sequentially, so the suite runs as one unit
            // in the main process at its suite index.
            if ($test instanceof PhptIterativeTestSuite) {
                $standalone[] = [
                    'index' => $index,
                    'test'  => $test,
                ];

                $index++;

                continue;
            }

            // The repetitions of a repeated test method and the attempts of a
            // retried test method are orchestrated by their suite's runTests()
            // method; the suite therefore travels as one atomic member of its
            // class' work unit instead of being flattened into its tests.
            if ($test instanceof IterativeTestSuite) {
                $tests = $test->tests();

                assert($tests !== [] && $tests[0] instanceof TestCase);

                $className = $tests[0]::class;

                if (!isset($byClass[$className])) {
                    $byClass[$className] = [
                        'index' => $index,
                        'tests' => [],
                    ];

                    $index++;
                }

                $byClass[$className]['tests'][] = $test;

                continue;
            }

            // The tests of a data provider method travel as their
            // DataProviderTestSuite so that the suite's "test suite started" /
            // "test suite finished" envelope, which nests the tests in the
            // logger output of a sequential run, is emitted inside the worker
            // (or by the in-process run) in a parallel run, too.
            if ($test instanceof DataProviderTestSuite) {
                [$className] = explode('::', $test->name());

                assert(class_exists($className) && is_subclass_of($className, TestCase::class));

                if (!isset($byClass[$className])) {
                    $byClass[$className] = [
                        'index' => $index,
                        'tests' => [],
                    ];

                    $index++;
                }

                $byClass[$className]['tests'][] = $test;

                continue;
            }

            if ($test instanceof TestSuite) {
                $this->collect($test, $byClass, $phpt, $standalone, $index);

                continue;
            }

            if ($test instanceof TestCase) {
                $className = $test::class;

                if (!isset($byClass[$className])) {
                    $byClass[$className] = [
                        'index' => $index,
                        'tests' => [],
                    ];

                    $index++;
                }

                $byClass[$className]['tests'][] = $test;

                continue;
            }

            if ($test instanceof PhptTestCase) {
                $file = $test->toString();

                // A PHPT test cannot carry the #[DoNotRunInParallel] attribute,
                // so it declares any tests it must not run alongside with a
                // --CONFLICTS-- section. The runner honours those conflict keys
                // while running the PHPT tests concurrently in the main process.
                $phpt[] = [
                    'index'     => $index,
                    'file'      => $file,
                    'conflicts' => $this->phptConflicts($file),
                ];

                $index++;

                continue;
            }

            // Any other kind of test cannot be reconstructed in a worker and is
            // run as a standalone unit in the main process.
            // @codeCoverageIgnoreStart
            $standalone[] = [
                'index' => $index,
                'test'  => $test,
            ];

            $index++;
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * The conflict keys a PHPT test declares with a --CONFLICTS-- section. While
     * a test that conflicts with a key is running, no other test that conflicts
     * with the same key may run; the reserved key "all" conflicts with every
     * other test. A test with no such section declares no conflicts.
     *
     * @param non-empty-string $file
     *
     * @return list<non-empty-string>
     */
    private function phptConflicts(string $file): array
    {
        $parser = new Parser;

        try {
            $sections = $parser->parse($file);
            // @codeCoverageIgnoreStart
        } catch (PhptException) {
            // A malformed PHPT cannot meaningfully declare conflicts; it is run
            // anyway and reports its own parse error at its suite position.
            return [];
            // @codeCoverageIgnoreEnd
        }

        if (!isset($sections['CONFLICTS'])) {
            return [];
        }

        return $parser->parseConflictsSection($sections['CONFLICTS']);
    }
}
