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

use function file;
use function get_parent_class;
use function gettype;
use function is_array;
use function is_object;
use function is_resource;
use function mt_srand;
use function preg_match;
use function serialize;
use function spl_object_id;
use PHPUnit\Event;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestRunner\ChildProcessResultProcessor;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\Runner\Parallel\CompletedWorkUnit;
use PHPUnit\Runner\Parallel\PersistentWorker;
use PHPUnit\Runner\Parallel\PhptRunner;
use PHPUnit\Runner\Parallel\PhptWorkUnit;
use PHPUnit\Runner\Parallel\ResultAggregator;
use PHPUnit\Runner\Parallel\TestClassWorkUnit;
use PHPUnit\Runner\Parallel\WorkerException;
use PHPUnit\Runner\Parallel\WorkerPool;
use PHPUnit\Runner\Parallel\WorkUnit;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use PHPUnit\Runner\ResultCache\ResultCache;
use PHPUnit\Runner\TestSuiteSorter;
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
     * @throws RuntimeException
     */
    public function run(Configuration $configuration, ResultCache $resultCache, TestSuite $suite): void
    {
        try {
            Event\Facade::emitter()->testRunnerStarted();

            if ($configuration->executionOrder() === TestSuiteSorter::ORDER_RANDOMIZED) {
                mt_srand($configuration->randomOrderSeed());
            }

            if ($configuration->executionOrder() !== TestSuiteSorter::ORDER_DEFAULT ||
                $configuration->executionOrderDefects() !== TestSuiteSorter::ORDER_DEFAULT ||
                $configuration->resolveDependencies()) {
                $resultCache->load();

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

            $collected = $this->collectUnits($suite);

            if ($collected['units'] !== [] || $collected['phpt'] !== [] || $collected['standalone'] !== []) {
                $this->execute($configuration, $collected['units'], $collected['phpt'], $collected['standalone']);
            }

            Event\Facade::emitter()->testRunnerExecutionFinished();
            Event\Facade::emitter()->testRunnerFinished();
        } catch (Throwable $t) {
            throw new RuntimeException(
                $t->getMessage(),
                (int) $t->getCode(),
                $t,
            );
        }
    }

    /**
     * Units are run in two phases.
     *
     * The parallel phase distributes across the worker pool every unit whose
     * tests may run in a worker process: those that are neither attributed with
     * #[DoNotRunInParallel] nor carry test data that cannot be serialized for
     * transport to a worker.
     *
     * The in-process phase then runs the remaining units one at a time in the
     * main PHPUnit process, after the parallel phase has finished so that they
     * never overlap a worker. A unit runs here when it is attributed with
     * #[DoNotRunInParallel] (the author has declared that its tests must not run
     * alongside others, for instance because they share a process-global
     * resource), when it is configured to run in a separate process (an
     * isolation that a shared worker cannot provide but the main process can),
     * or when its test data cannot be serialized for transport to a worker (in
     * which case the main process is the only place it can run at all). Running
     * in the main process is ordinary execution that behaves exactly as it would
     * in sequential mode.
     *
     * Standalone tests that are not PHPUnit\Framework\TestCase instances — most
     * commonly PHPT tests — cannot be reconstructed in a worker and are likewise
     * run in the main process, each at its own suite index.
     *
     * @param list<WorkUnit>                                   $units
     * @param list<PhptWorkUnit>                               $phpt
     * @param list<array{index: non-negative-int, test: Test}> $standalone
     *
     * @throws WorkerException
     */
    private function execute(Configuration $configuration, array $units, array $phpt, array $standalone): void
    {
        $aggregator = new ResultAggregator(
            Event\Facade::instance(),
            Event\Facade::emitter(),
            PassedTests::instance(),
            CodeCoverage::instance(),
        );

        $parallel = [];

        $processIsolation = $configuration->processIsolation();

        foreach ($units as $unit) {
            if ($unit instanceof TestClassWorkUnit &&
                ($processIsolation ||
                 $this->mustNotRunInParallel($unit) ||
                 $this->requiresProcessIsolation($unit) ||
                 !$this->canBeSerialized($unit))) {
                // The unit keeps its global suite index; the aggregator runs it
                // in the main process at the moment that index comes up in the
                // release sequence, which keeps the output in global suite order.
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

        foreach ($standalone as $item) {
            $test = $item['test'];

            $aggregator->registerInProcessUnit(
                $item['index'],
                static function () use ($test): void
                {
                    $test->run();
                },
            );
        }

        // The PHPT tests run concurrently in the main process, each as its own
        // child process rather than nested inside a worker. Every one is
        // registered with the aggregator so that the events it collected are
        // replayed at its suite index, interspersed in global suite order with
        // the worker units and the in-process units.
        if ($phpt !== []) {
            $this->runPhpt($configuration, $phpt, $aggregator);
        }

        // Run any in-process units that precede the first worker unit, then the
        // worker units (whose completions drive the release of the in-process
        // units interspersed among them), then any that follow the last one.
        $aggregator->flush();

        if ($parallel !== []) {
            $this->runInParallel($parallel, $aggregator, $configuration->numberOfParallelWorkers());
        }

        $aggregator->flush();
    }

    /**
     * Run the PHPT tests concurrently, each as its own child process in the
     * main process, and register every one with the aggregator so that the
     * events it collected are replayed at its suite index, in global suite
     * order.
     *
     * @param non-empty-list<PhptWorkUnit> $phpt
     */
    private function runPhpt(Configuration $configuration, array $phpt, ResultAggregator $aggregator): void
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

        new PhptRunner(new JobRunner($processor), $concurrency)->run(
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

                // Release everything that has become contiguous in suite order
                // so that progress is reported as the PHPT tests finish, rather
                // than buffered until the whole phase is done. PHPT tests finish
                // out of order, so this releases nothing until the next test in
                // suite order is among those that have finished.
                $aggregator->flush();
            },
        );
    }

    /**
     * @param non-empty-list<WorkUnit> $units
     * @param positive-int             $numberOfWorkers
     *
     * @throws WorkerException
     */
    private function runInParallel(array $units, ResultAggregator $aggregator, int $numberOfWorkers): void
    {
        $pool = $this->createPool($numberOfWorkers);

        $pool->start();

        try {
            $pool->run(
                $units,
                static function (CompletedWorkUnit $completed) use ($aggregator): void
                {
                    $aggregator->add($completed);
                },
            );
        } finally {
            $pool->stop();
        }
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
        foreach ($unit->tests() as $test) {
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

        foreach ($unit->tests() as $test) {
            if (MetadataRegistry::parser()->forMethod($className, $test->name())->isDoNotRunInParallel()->isNotEmpty()) {
                return true;
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

        foreach ($unit->tests() as $test) {
            if (MetadataRegistry::parser()->forMethod($className, $test->name())->isRunInSeparateProcess()->isNotEmpty()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param positive-int $numberOfWorkers
     */
    private function createPool(int $numberOfWorkers): WorkerPool
    {
        $processor = new ChildProcessResultProcessor(
            Event\Facade::instance(),
            Event\Facade::emitter(),
            PassedTests::instance(),
            CodeCoverage::instance(),
        );

        $jobRunner = new JobRunner($processor);

        $workers = [new PersistentWorker($jobRunner, $processor, 0)];

        for ($id = 1; $id < $numberOfWorkers; $id++) {
            $workers[] = new PersistentWorker($jobRunner, $processor, $id);
        }

        return new WorkerPool($workers);
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
     * @return array{units: list<WorkUnit>, phpt: list<PhptWorkUnit>, standalone: list<array{index: non-negative-int, test: Test}>}
     */
    private function collectUnits(TestSuite $suite): array
    {
        /** @var array<class-string<TestCase>, array{index: non-negative-int, tests: list<TestCase>}> $byClass */
        $byClass = [];

        /** @var list<array{index: non-negative-int, file: non-empty-string}> $phpt */
        $phpt = [];

        /** @var list<array{index: non-negative-int, test: Test}> $standalone */
        $standalone = [];

        $index = 0;

        $this->collect($suite, $byClass, $phpt, $standalone, $index);

        $units = [];

        foreach ($byClass as $className => $group) {
            $units[] = new TestClassWorkUnit($group['index'], $className, $group['tests']);
        }

        $phptUnits = [];

        foreach ($phpt as $item) {
            $phptUnits[] = new PhptWorkUnit($item['index'], $item['file']);
        }

        return [
            'units'      => $units,
            'phpt'       => $phptUnits,
            'standalone' => $standalone,
        ];
    }

    /**
     * @param array<class-string<TestCase>, array{index: non-negative-int, tests: list<TestCase>}> $byClass
     * @param list<array{index: non-negative-int, file: non-empty-string}>                         $phpt
     * @param list<array{index: non-negative-int, test: Test}>                                     $standalone
     * @param non-negative-int                                                                     $index
     */
    private function collect(TestSuite $suite, array &$byClass, array &$phpt, array &$standalone, int &$index): void
    {
        foreach ($suite as $test) {
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

                if ($this->phptMustNotRunInParallel($file)) {
                    // A PHPT test cannot carry the #[DoNotRunInParallel]
                    // attribute, so it opts out with a --DO_NOT_RUN_IN_PARALLEL--
                    // section, which routes it to the main process.
                    $standalone[] = [
                        'index' => $index,
                        'test'  => $test,
                    ];
                } else {
                    // A PHPT test is not a PHPUnit\Framework\TestCase, but a
                    // worker can reconstruct it from its file path alone and run
                    // it just like the main process would.
                    $phpt[] = [
                        'index' => $index,
                        'file'  => $file,
                    ];
                }

                $index++;

                continue;
            }

            // Any other kind of test cannot be reconstructed in a worker and is
            // run as a standalone unit in the main process.
            $standalone[] = [
                'index' => $index,
                'test'  => $test,
            ];

            $index++;
        }
    }

    /**
     * Whether a PHPT test declares, with a --DO_NOT_RUN_IN_PARALLEL-- section,
     * that it must not run in parallel — typically because it relies on a shared
     * resource such as a fixed temporary file or the result cache.
     */
    private function phptMustNotRunInParallel(string $file): bool
    {
        $lines = @file($file);

        if ($lines === false) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        foreach ($lines as $line) {
            if (preg_match('/^--DO_NOT_RUN_IN_PARALLEL--/', $line) === 1) {
                return true;
            }
        }

        return false;
    }
}
