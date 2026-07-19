<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Parallel;

use function array_pop;
use function array_reverse;
use function array_slice;
use function assert;
use function sprintf;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\TestMethodBuilder;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Finished as TestFinished;
use PHPUnit\Event\TestRunner\ChildProcessReason;
use PHPUnit\Event\TestSuite\Finished as TestSuiteFinished;
use PHPUnit\Event\TestSuite\Started as TestSuiteStarted;
use PHPUnit\Event\TestSuite\TestSuite as TestSuiteValue;
use PHPUnit\Event\TestSuite\TestSuiteBuilder;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestRunner\ChildProcessResultEnvelope;
use PHPUnit\Framework\TestSuite as FrameworkTestSuite;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestRunner\TestResult\PassedTests;

/**
 * Collects the results that the workers produce and replays them into the
 * parent process' event subsystem.
 *
 * Workers finish their units out of order, but every printer, logger, and
 * report assumes the suite-ordered arrival it sees in sequential mode. The
 * aggregator therefore buffers each finished unit and forwards it only once all
 * of the units that precede it in suite order have been forwarded; the result
 * is an event stream that is byte-for-byte identical to the sequential one,
 * which keeps CI diffs stable.
 *
 * A worker does not only report a unit when the whole unit has finished: while
 * the unit is still running, it streams the events of every test that finishes,
 * and those partial event streams reach the aggregator through
 * addStreamedEvents(). The events streamed by the unit that is next in suite
 * order are forwarded immediately — that unit is what the ordered output is
 * waiting for, so its progress can be shown live — while the events streamed by
 * any later unit are buffered and forwarded when their unit's turn comes. This
 * is what makes progress output appear per finished test instead of stalling
 * until an entire test class has finished, without giving up the ordering
 * guarantee.
 *
 * Forwarding a unit means replaying its collected event stream through the
 * parent dispatcher, importing the tests it recorded as passed, and merging its
 * code coverage. Because every user-facing output is a downstream subscriber of
 * these events, the parent's entire output, logging, and result subsystem works
 * unchanged.
 *
 * Some units do not run in a worker but in the main process (see
 * ParallelTestRunner): a unit attributed with #[DoNotRunInParallel], one that
 * needs process isolation, or one whose data cannot be serialized. Such a unit
 * is registered with its suite index and run, by the aggregator, at the moment
 * its index comes up in the release sequence. Running it there — between the
 * units that precede and follow it in suite order — lets its events reach the
 * dispatcher live and still in global suite order, so the ordering guarantee
 * holds for every output format, not just the ones that re-sort.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ResultAggregator
{
    private readonly Facade $eventFacade;
    private readonly Emitter $emitter;
    private readonly PassedTests $passedTests;
    private readonly CodeCoverage $codeCoverage;

    /**
     * Whether the results collected so far call for the run to stop
     * (--stop-on-*). Once this reports true, nothing further is released:
     * the results that have not been forwarded yet are for tests that a
     * sequential run would not have run.
     *
     * @var callable(): bool
     */
    private $shouldStop;

    /**
     * @var array<non-negative-int, CompletedWorkUnit>
     */
    private array $buffer = [];

    /**
     * @var array<non-negative-int, callable():void>
     */
    private array $inProcessRunners = [];

    /**
     * The indexes of the registered in-process units that must run alone —
     * their test class is attributed with #[DoNotRunInParallel]. The release
     * sequence does not run such a unit itself: it stops at the unit's index,
     * hasPendingExclusiveUnit() reports the stop, and the runner invokes the
     * unit through runPendingExclusiveUnit() once nothing else is executing.
     *
     * @var array<non-negative-int, true>
     */
    private array $exclusiveUnits = [];

    /**
     * The events streamed by units that are still running, buffered per suite
     * index until their unit's turn in the release sequence comes.
     *
     * @var array<non-negative-int, list<EventCollection>>
     */
    private array $streamedEvents = [];

    /**
     * The indexes of the units whose streamed events have been forwarded.
     * Such a unit can no longer be re-run from scratch: some of its results
     * were already reported, and running it again would report them twice.
     *
     * @var array<non-negative-int, true>
     */
    private array $forwardedStreamedEvents = [];

    /**
     * What the forwarded streamed frames of each in-flight unit have already
     * reported: how many times each test finished (a repeated test finishes
     * once per repetition), and the test-suite envelopes that were opened but
     * not yet closed, outermost first.
     *
     * When a unit produces no trustworthy result — its worker died, or its
     * result envelope failed verification — this is what tells the aggregator
     * which of the unit's tests still have to be reported as errored and
     * which envelopes still have to be closed, so that the event stream stays
     * complete and balanced.
     *
     * @var array<non-negative-int, array<non-empty-string, positive-int>>
     */
    private array $forwardedFinishedTests = [];

    /**
     * @var array<non-negative-int, list<TestSuiteValue>>
     */
    private array $forwardedOpenSuites = [];

    /**
     * @var non-negative-int
     */
    private int $nextIndex = 0;

    /**
     * @param callable(): bool $shouldStop
     */
    public function __construct(Facade $eventFacade, Emitter $emitter, PassedTests $passedTests, CodeCoverage $codeCoverage, callable $shouldStop)
    {
        $this->eventFacade  = $eventFacade;
        $this->emitter      = $emitter;
        $this->passedTests  = $passedTests;
        $this->codeCoverage = $codeCoverage;
        $this->shouldStop   = $shouldStop;
    }

    /**
     * Register a unit that is to be run, in the main process, at the point its
     * suite index comes up in the release sequence.
     *
     * An exclusive unit — one whose test class is attributed with
     * #[DoNotRunInParallel] — additionally runs alone: the release sequence
     * stops at its index instead of running it, and the runner invokes it
     * through runPendingExclusiveUnit() once nothing else is executing.
     *
     * @param non-negative-int $index
     * @param callable():void  $runner
     */
    public function registerInProcessUnit(int $index, callable $runner, bool $exclusive = false): void
    {
        $this->inProcessRunners[$index] = $runner;

        if ($exclusive) {
            $this->exclusiveUnits[$index] = true;
        }
    }

    /**
     * Whether the release sequence has stopped at the index of an exclusive
     * unit: everything that precedes the unit in suite order has been
     * forwarded, and the unit waits to be run through
     * runPendingExclusiveUnit() once nothing else is executing.
     */
    public function hasPendingExclusiveUnit(): bool
    {
        return isset($this->exclusiveUnits[$this->nextIndex], $this->inProcessRunners[$this->nextIndex]);
    }

    /**
     * Run the exclusive unit that the release sequence has stopped at, then
     * resume releasing. The caller is responsible for calling this only once
     * nothing else is executing — that is what makes the unit's execution
     * exclusive.
     */
    public function runPendingExclusiveUnit(): void
    {
        assert(isset($this->inProcessRunners[$this->nextIndex]));
        assert(isset($this->exclusiveUnits[$this->nextIndex]));

        $runner = $this->inProcessRunners[$this->nextIndex];

        unset($this->inProcessRunners[$this->nextIndex], $this->exclusiveUnits[$this->nextIndex]);

        $runner();

        $this->nextIndex++;

        $this->release();
    }

    /**
     * Accept a finished worker unit and release every unit that has now become
     * releasable in suite order.
     */
    public function add(CompletedWorkUnit $completed): void
    {
        $this->buffer[$completed->unit()->index()] = $completed;

        $this->release();
    }

    /**
     * Accept events that a worker streamed while its unit is still running.
     *
     * The events of the unit that is next in suite order are forwarded
     * immediately: every event that precedes them has already been forwarded,
     * so showing them live cannot violate the ordering guarantee. The events
     * of any later unit are buffered and forwarded when its turn comes.
     *
     * @param non-negative-int $index
     */
    public function addStreamedEvents(int $index, EventCollection $events): void
    {
        // Events that arrive after the collected results have called for the
        // run to stop are buffered rather than forwarded; the release
        // sequence is frozen, so they will never be shown. Their tests are
        // ones that a sequential run would not have run.
        if ($index === $this->nextIndex && !($this->shouldStop)()) {
            $this->forwardedStreamedEvents[$index] = true;

            $this->forwardFrame($index, $events);

            return;
        }

        if (!isset($this->streamedEvents[$index])) {
            $this->streamedEvents[$index] = [];
        }

        $this->streamedEvents[$index][] = $events;
    }

    /**
     * Whether the unit at the given index may be re-run from scratch: true
     * when none of its streamed events have been forwarded yet. The events
     * that were buffered for it are discarded, so that the events of the new
     * attempt take their place. The worker pool makes this call before it
     * retries a crashed unit on a fresh worker, because the retry re-runs all
     * of the unit's tests and must not repeat any that were already reported.
     *
     * @param non-negative-int $index
     */
    public function discardStreamedEventsFor(int $index): bool
    {
        if (isset($this->forwardedStreamedEvents[$index])) {
            return false;
        }

        unset($this->streamedEvents[$index]);

        return true;
    }

    /**
     * Release every unit that is releasable in suite order, running registered
     * in-process units in place as their index comes up. Called both after a
     * worker finishes and directly by the runner, so that in-process units that
     * precede or follow all worker units are run even when no worker completion
     * drives the release.
     */
    public function flush(): void
    {
        $this->release();
    }

    private function release(): void
    {
        while (true) {
            // Freeze the release sequence once the collected results call for
            // the run to stop: like the sequential runner, which checks
            // between two tests whether it should go on, the aggregator checks
            // between two releases. Returning here also leaves the streamed
            // events of the unit that is next in line unforwarded.
            if (($this->shouldStop)()) {
                return;
            }

            if (isset($this->buffer[$this->nextIndex])) {
                $this->forward($this->buffer[$this->nextIndex]);

                unset($this->buffer[$this->nextIndex]);

                $this->nextIndex++;

                continue;
            }

            if (isset($this->inProcessRunners[$this->nextIndex])) {
                // An exclusive unit is not run from here: the release
                // sequence stops, and the runner invokes the unit through
                // runPendingExclusiveUnit() once nothing else is executing.
                if (isset($this->exclusiveUnits[$this->nextIndex])) {
                    break;
                }

                $runner = $this->inProcessRunners[$this->nextIndex];

                unset($this->inProcessRunners[$this->nextIndex]);

                $runner();

                $this->nextIndex++;

                continue;
            }

            break;
        }

        // The unit that the release sequence now waits for may have streamed
        // events while it was buffered behind its predecessors; with the
        // predecessors forwarded, those events are next in suite order and are
        // forwarded now. Events it streams from here on are forwarded live.
        $this->forwardStreamedEventsOf($this->nextIndex);
    }

    /**
     * @param non-negative-int $index
     */
    private function forwardStreamedEventsOf(int $index): void
    {
        if (!isset($this->streamedEvents[$index])) {
            return;
        }

        $this->forwardedStreamedEvents[$index] = true;

        foreach ($this->streamedEvents[$index] as $events) {
            $this->forwardFrame($index, $events);
        }

        unset($this->streamedEvents[$index]);
    }

    /**
     * Forward one streamed frame, recording what it reports: which tests it
     * finished, and which test-suite envelopes it opened or closed. Should
     * the unit fail to produce a trustworthy result later, this record is
     * what completes its event stream (see reportTestsWithoutResult()).
     *
     * @param non-negative-int $index
     */
    private function forwardFrame(int $index, EventCollection $events): void
    {
        if (!isset($this->forwardedFinishedTests[$index])) {
            $this->forwardedFinishedTests[$index] = [];
        }

        if (!isset($this->forwardedOpenSuites[$index])) {
            $this->forwardedOpenSuites[$index] = [];
        }

        foreach ($events as $event) {
            if ($event instanceof TestFinished) {
                $id = $event->test()->id();

                if (!isset($this->forwardedFinishedTests[$index][$id])) {
                    $this->forwardedFinishedTests[$index][$id] = 1;
                } else {
                    $this->forwardedFinishedTests[$index][$id]++;
                }

                continue;
            }

            if ($event instanceof TestSuiteStarted) {
                $this->forwardedOpenSuites[$index][] = $event->testSuite();

                continue;
            }

            if ($event instanceof TestSuiteFinished) {
                array_pop($this->forwardedOpenSuites[$index]);
            }
        }

        $this->eventFacade->forward($events);
    }

    private function forward(CompletedWorkUnit $completed): void
    {
        // Whatever the outcome of the unit, the events it streamed while it
        // was running precede that outcome in suite order — for a crashed
        // unit, they are the tests that did complete before the worker died.
        $this->forwardStreamedEventsOf($completed->unit()->index());

        if ($completed->crashed()) {
            $message = $completed->message();

            if ($message === null || $message === '') {
                $message = sprintf(
                    'The worker process running %s ended unexpectedly',
                    $completed->unit()->name(),
                );
            }

            $this->reportTestsWithoutResult($completed, $message);

            return;
        }

        $serializedResult = ChildProcessResultEnvelope::verifyAndStripNonce(
            $completed->serializedResult(),
            $completed->nonce(),
        );

        if ($serializedResult === null) {
            $message = sprintf(
                'The result of the worker process running %s was tampered with or written by an unexpected process',
                $completed->unit()->name(),
            );

            $this->reportTestsWithoutResult($completed, $message);

            return;
        }

        $childResult = ChildProcessResultEnvelope::decode($serializedResult);

        if ($childResult === null) {
            $message = sprintf(
                'The worker process running %s ended unexpectedly',
                $completed->unit()->name(),
            );

            $this->reportTestsWithoutResult($completed, $message);

            return;
        }

        assert($childResult->events instanceof EventCollection);
        assert($childResult->passedTests instanceof PassedTests);

        $this->eventFacade->forward($childResult->events);
        $this->passedTests->import($childResult->passedTests);

        ChildProcessResultEnvelope::mergeCodeCoverage($childResult, $this->codeCoverage);

        unset($this->forwardedFinishedTests[$completed->unit()->index()], $this->forwardedOpenSuites[$completed->unit()->index()]);
    }

    /**
     * Report every test of a unit whose result will never arrive, because the
     * worker running it died or its result envelope failed verification.
     *
     * The tests that were already reported through the unit's forwarded
     * streamed frames keep the results that were shown for them; every other
     * test of the unit is reported as errored, the way the sequential runner
     * reports a test whose child process ended unexpectedly. Without this,
     * the unit's remaining tests would silently disappear from the results:
     * loggers, the testdox report, and the counts in the summary would all
     * miss them.
     *
     * The test-suite envelopes that the forwarded frames left open — at
     * least the class-level envelope, when any frame was forwarded — are
     * closed, so that consumers that reconstruct the suite hierarchy from
     * paired Started/Finished events (JUnit XML, TeamCity) see a balanced
     * stream. When nothing was forwarded, the class-level envelope is
     * emitted here, around the errored tests, as it would have been by the
     * unit itself.
     *
     * @param non-empty-string $message
     */
    private function reportTestsWithoutResult(CompletedWorkUnit $completed, string $message): void
    {
        $unit  = $completed->unit();
        $index = $unit->index();

        if (!$unit instanceof TestClassWorkUnit) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $finished = [];

        if (isset($this->forwardedFinishedTests[$index])) {
            $finished = $this->forwardedFinishedTests[$index];
        }

        $openSuites = [];

        if (isset($this->forwardedOpenSuites[$index])) {
            $openSuites = $this->forwardedOpenSuites[$index];
        }

        if ($openSuites === []) {
            $suite = FrameworkTestSuite::empty($unit->className());

            foreach ($unit->tests() as $test) {
                $suite->addTest($test);
            }

            $classSuite = TestSuiteBuilder::from($suite);

            $this->emitter->testSuiteStarted($classSuite);
        } else {
            $classSuite = $openSuites[0];
        }

        $innerOpenSuites = array_slice($openSuites, 1);

        $throwable = ThrowableBuilder::from(new AssertionFailedError($message));

        $members  = [];
        $anyStubs = false;

        foreach ($unit->tests() as $test) {
            $stubs = [];

            $this->collectUnreportedLeavesOf($test, $finished, $stubs);

            $members[] = ['test' => $test, 'stubs' => $stubs];

            if ($stubs !== []) {
                $anyStubs = true;
            }
        }

        // Every test of the unit was already reported through its streamed
        // frames; the child-process failure that cost the unit its result
        // envelope is still signalled.
        if (!$anyStubs) {
            $this->emitter->childProcessErrored(ChildProcessReason::ParallelWorker, $message);
        }

        foreach ($members as $member) {
            $test = $member['test'];

            // A test whose result never arrived is reported with the same
            // three events that the sequential runner emits for a test whose
            // child process ended unexpectedly.
            foreach ($member['stubs'] as $testMethod) {
                $this->emitter->childProcessErrored(ChildProcessReason::ParallelWorker, $message);
                $this->emitter->testErrored($testMethod, $throwable);
                $this->emitter->testFinished($testMethod, 0);
            }

            // The member whose envelopes the streamed frames left open is the
            // one that was executing when the results stopped coming. Its
            // envelopes are closed right after its remaining tests have been
            // reported, so that the tests of the members that follow it are
            // not nested inside them.
            if ($innerOpenSuites !== [] && !$test instanceof TestCase && $test->name() === $innerOpenSuites[0]->name()) {
                foreach (array_reverse($innerOpenSuites) as $innerSuite) {
                    $this->emitter->testSuiteFinished($innerSuite);
                }

                $innerOpenSuites = [];
            }
        }

        foreach (array_reverse($innerOpenSuites) as $innerSuite) {
            // @codeCoverageIgnoreStart
            $this->emitter->testSuiteFinished($innerSuite);
            // @codeCoverageIgnoreEnd
        }

        $this->emitter->testSuiteFinished($classSuite);

        unset($this->forwardedFinishedTests[$index], $this->forwardedOpenSuites[$index]);
    }

    /**
     * Collect the value objects of every test case of the given unit member
     * that has not already finished. A member is a single test case or a
     * suite (the tests of a data provider method, the repetitions of a
     * repeated test method, the attempts of a retried test method) whose test
     * cases are visited recursively.
     *
     * The record of already-finished tests counts how many times a test
     * finished, and every visited test case consumes one of its finishes: the
     * repetitions of a repeated test method share one test id, and each
     * repetition that finished must excuse only one of them.
     *
     * @param array<non-empty-string, int> $finished
     * @param list<TestMethod>             $stubs
     */
    private function collectUnreportedLeavesOf(Test $test, array &$finished, array &$stubs): void
    {
        if ($test instanceof TestCase) {
            $testMethod = TestMethodBuilder::fromTestCase($test);
            $id         = $testMethod->id();

            if (isset($finished[$id]) && $finished[$id] > 0) {
                $finished[$id]--;

                return;
            }

            $stubs[] = $testMethod;

            return;
        }

        assert($test instanceof FrameworkTestSuite);

        foreach ($test->tests() as $member) {
            $this->collectUnreportedLeavesOf($member, $finished, $stubs);
        }
    }
}
