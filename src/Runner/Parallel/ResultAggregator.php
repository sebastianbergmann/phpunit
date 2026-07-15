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

use function property_exists;
use function sprintf;
use function unserialize;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade;
use PHPUnit\Framework\TestRunner\ChildProcessResultEnvelope;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestRunner\TestResult\PassedTests;
use stdClass;

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
     * @param non-negative-int $index
     * @param callable():void  $runner
     */
    public function registerInProcessUnit(int $index, callable $runner): void
    {
        $this->inProcessRunners[$index] = $runner;
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

            $this->eventFacade->forward($events);

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
            $this->eventFacade->forward($events);
        }

        unset($this->streamedEvents[$index]);
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

            $this->emitter->childProcessErrored();
            $this->emitter->testRunnerTriggeredPhpunitWarning($message);

            return;
        }

        $serializedResult = ChildProcessResultEnvelope::verifyAndStripNonce(
            $completed->serializedResult(),
            $completed->nonce(),
        );

        if ($serializedResult === null) {
            $this->emitter->childProcessErrored();
            $this->emitter->testRunnerTriggeredPhpunitWarning(
                sprintf(
                    'The result of the worker process running %s was tampered with or written by an unexpected process',
                    $completed->unit()->name(),
                ),
            );

            return;
        }

        $childResult = @unserialize($serializedResult);

        if (!$childResult instanceof stdClass ||
            !property_exists($childResult, 'events') ||
            !property_exists($childResult, 'passedTests') ||
            !$childResult->events instanceof EventCollection ||
            !$childResult->passedTests instanceof PassedTests) {
            $this->emitter->childProcessErrored();
            $this->emitter->testRunnerTriggeredPhpunitWarning(
                sprintf(
                    'The worker process running %s ended unexpectedly',
                    $completed->unit()->name(),
                ),
            );

            return;
        }

        $this->eventFacade->forward($childResult->events);
        $this->passedTests->import($childResult->passedTests);

        ChildProcessResultEnvelope::mergeCodeCoverage($childResult, $this->codeCoverage);
    }
}
