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

use function assert;
use function usleep;
use PHPUnit\Event\EventCollection;

/**
 * A fixed-size pool of PersistentWorkers across which units of work are
 * distributed.
 *
 * Distribution is a dynamic work-stealing queue rather than a static
 * pre-partitioning of the units: whenever a worker becomes idle it pulls the
 * next unit from the queue, which self-balances the load against stragglers.
 *
 * The queue hands out its units in the order in which they are to be
 * dispatched: longest first, except on the slot that is reserved for the suite
 * order, which dispatches the unit the ordered output is waiting for, so that
 * the results keep flowing (see SUITE_ORDER_SLOTS and DispatchQueue).
 *
 * A single thread of control keeps all of the workers busy by polling them in
 * rounds: each round it drains the events that the busy workers have streamed
 * so far, harvests every worker that has finished its unit, hands both to the
 * caller-supplied callbacks, tops the idle workers up with more work, and
 * sleeps briefly before the next round if none finished.
 * A worker signals completion through the filesystem (see PersistentWorker),
 * which is polled rather than waited on with stream_select() because the latter
 * does not work on the workers' output pipes on Windows.
 *
 * If a worker dies, the unit it was running is retried once, on a fresh worker
 * process booted in place of the dead one — the crash may have been caused by
 * the state the dead process had accumulated, so the retry gets a pristine
 * environment. A unit whose retry also crashes, or that cannot be retried
 * because some of its results were already reported, is reported to the
 * callback as a crashed unit; its worker stays dead and the remaining units
 * are redistributed across the surviving workers. Should every worker die,
 * the units that were never started are likewise reported as crashed so that
 * the caller can account for all of them.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class WorkerPool
{
    /**
     * How long to sleep, in microseconds, when a polling round finds that no
     * worker has finished, so that waiting on the workers does not spin the CPU.
     */
    private const int POLL_INTERVAL_MICROSECONDS = 1000;

    /**
     * How many of the pool's dispatch slots are reserved for the suite order
     * (see DispatchQueue).
     *
     * One is enough here: a worker streams the events of every test of its unit
     * as that test finishes, so the unit the ordered output is waiting for
     * advances the release sequence while it is still executing. A second
     * reserved slot would therefore buy the output next to nothing, and would
     * cost the straggler protection that the cost order provides on it. The
     * PHPT runner, whose units report only once they have finished, reserves
     * more.
     */
    private const int SUITE_ORDER_SLOTS = 1;

    /**
     * @var non-empty-list<PersistentWorker>
     */
    private readonly array $workers;

    /**
     * The units that have not been dispatched to a worker yet, in dispatch
     * order.
     */
    private DispatchQueue $queue;

    /**
     * @var ?callable(CompletedWorkUnit):void
     */
    private $onCompleted;

    /**
     * @var ?callable(WorkUnit, EventCollection):void
     */
    private $onStreamedEvents;

    /**
     * Whether a unit whose worker died may be re-run from scratch. The caller
     * vetoes the retry when some of the unit's results have already been
     * reported — re-running the unit would then report them twice — and uses
     * the call to discard the unit's buffered results, so that the retry's
     * results take their place.
     *
     * @var ?callable(WorkUnit): bool
     */
    private $onCrashedUnitRetry;

    /**
     * The indexes of the units that have already been retried after a crash,
     * so that a unit whose retry crashes as well is reported as crashed
     * instead of being retried forever.
     *
     * @var array<non-negative-int, true>
     */
    private array $retriedUnits = [];

    /**
     * The budget of concurrently executing units that the pool shares with the
     * PHPT runner: a slot is taken for every dispatched unit and given back
     * when the unit finishes, so that the pool and the PHPT tests together
     * never execute more units at once than the budget allows.
     */
    private readonly ProcessBudget $budget;

    /**
     * @param non-empty-list<PersistentWorker> $workers
     */
    public function __construct(array $workers, ProcessBudget $budget)
    {
        $this->workers = $workers;
        $this->budget  = $budget;
        $this->queue   = new DispatchQueue([], self::SUITE_ORDER_SLOTS);
    }

    /**
     * @throws WorkerException
     */
    public function start(): void
    {
        foreach ($this->workers as $worker) {
            $worker->start();
        }
    }

    /**
     * Run all of the given units across the pool, invoking the completion
     * callback once for each unit as it finishes (in completion order, not
     * suite order).
     *
     * While a unit is still running, the events that its worker has streamed
     * so far are handed to the streamed-events callback as they arrive, so
     * that the caller can report progress per finished test instead of per
     * finished unit. The events of a unit are always delivered before its
     * completion.
     *
     * @param list<WorkUnit>                           $units
     * @param callable(CompletedWorkUnit):void         $onCompleted
     * @param callable(WorkUnit, EventCollection):void $onStreamedEvents
     * @param callable(WorkUnit): bool                 $onCrashedUnitRetry
     */
    public function run(array $units, callable $onCompleted, callable $onStreamedEvents, callable $onCrashedUnitRetry): void
    {
        $this->begin($units, $onCompleted, $onStreamedEvents, $onCrashedUnitRetry);

        while (!$this->isFinished()) {
            // Sleep briefly when a polling round did not progress so that
            // waiting for the workers does not spin the CPU. A round in which
            // a worker finished is not slept on, so its freed slot is refilled
            // at once on the next iteration.
            if (!$this->tick()) {
                usleep(self::POLL_INTERVAL_MICROSECONDS);
            }
        }
    }

    /**
     * Accept the units to run without running them yet: the caller is expected
     * to drive the pool with tick() until isFinished() reports completion. This
     * is what lets the parallel test runner advance the worker pool and the
     * PHPT runner side by side in one polling loop.
     *
     * @param list<WorkUnit>                           $units
     * @param callable(CompletedWorkUnit):void         $onCompleted
     * @param callable(WorkUnit, EventCollection):void $onStreamedEvents
     * @param callable(WorkUnit): bool                 $onCrashedUnitRetry
     */
    public function begin(array $units, callable $onCompleted, callable $onStreamedEvents, callable $onCrashedUnitRetry): void
    {
        $this->queue              = new DispatchQueue($units, self::SUITE_ORDER_SLOTS);
        $this->onCompleted        = $onCompleted;
        $this->onStreamedEvents   = $onStreamedEvents;
        $this->onCrashedUnitRetry = $onCrashedUnitRetry;
        $this->retriedUnits       = [];
    }

    /**
     * Advance the pool by one polling round: top the idle workers up with
     * queued units, drain the events the busy workers have streamed, and
     * harvest every worker that has finished its unit. Returns whether the
     * round made progress; a caller driving the pool in a loop is expected to
     * sleep briefly when it did not, so that polling does not spin the CPU.
     *
     * When the caller passes false for $mayDispatch, no queued unit is handed
     * to a worker in this round: the units that are already executing are
     * still polled and harvested, so the pool drains. This is how the caller
     * makes room for a unit that must run alone (see ParallelTestRunner).
     */
    public function tick(bool $mayDispatch = true): bool
    {
        $onCompleted      = $this->onCompleted;
        $onStreamedEvents = $this->onStreamedEvents;

        assert($onCompleted !== null);
        assert($onStreamedEvents !== null);

        if ($mayDispatch) {
            $this->dispatch();
        }

        $busy = $this->busyWorkers();

        if ($busy === []) {
            // With no unit in flight, queued units can be waiting for one of
            // two reasons: every worker has died, or the shared process budget
            // is exhausted by units executing elsewhere. Only the former is
            // terminal; the abandoned units are then accounted for so that the
            // caller does not silently lose them.
            if ($this->hasQueuedUnits() && !$this->hasAliveWorkers()) {
                while ($this->hasQueuedUnits()) {
                    $onCompleted(CompletedWorkUnit::fromCrash($this->nextQueuedUnit()));
                }

                return true;
            }

            return false;
        }

        $progressed = false;

        foreach ($busy as $worker) {
            $completed = $worker->poll($onStreamedEvents);

            if ($completed === null) {
                continue;
            }

            $progressed = true;

            $this->budget->release();

            if ($completed->crashed() && $this->retry($worker, $completed->unit(), $onCompleted)) {
                continue;
            }

            $onCompleted($completed);
        }

        return $progressed;
    }

    /**
     * Whether every unit accepted by begin() has finished.
     */
    public function isFinished(): bool
    {
        return !$this->hasQueuedUnits() && $this->busyWorkers() === [];
    }

    /**
     * Whether any worker is currently executing a unit.
     */
    public function hasExecutingUnits(): bool
    {
        return $this->busyWorkers() !== [];
    }

    /**
     * Abandon the run: the units that have not been dispatched yet are dropped
     * and every worker that is busy executing a unit is terminated without
     * waiting for its result. Used when the test runner stops early, because
     * the results collected so far call for it (--stop-on-*); the workers that
     * are idle stay alive and are shut down by stop() as usual.
     */
    public function halt(): void
    {
        $this->queue->clear();

        foreach ($this->workers as $worker) {
            if (!$worker->isAlive() || !$worker->isBusy()) {
                continue;
            }

            $worker->kill();

            // The slot that the killed unit held goes back to the shared
            // process budget.
            $this->budget->release();
        }
    }

    public function stop(): void
    {
        foreach ($this->workers as $worker) {
            $worker->stop();
        }
    }

    /**
     * Retry a unit whose worker died, once, on a fresh worker process.
     *
     * The crash may have been caused by the state that the dead worker had
     * accumulated while running its earlier units, so the retry runs in a
     * pristine process, booted in place of the dead one. A unit is retried at
     * most once, and only while none of its results have been reported yet:
     * the retry re-runs all of the unit's tests, so it must not repeat any
     * that were already shown. The caller-supplied callback makes that call
     * and discards the results of the crashed attempt that were buffered.
     *
     * @param callable(CompletedWorkUnit):void $onCompleted
     *
     * @throws WorkerException
     */
    private function retry(PersistentWorker $worker, WorkUnit $unit, callable $onCompleted): bool
    {
        $onCrashedUnitRetry = $this->onCrashedUnitRetry;

        assert($onCrashedUnitRetry !== null);

        $index = $unit->index();

        if (isset($this->retriedUnits[$index])) {
            return false;
        }

        if (!$onCrashedUnitRetry($unit)) {
            return false;
        }

        $this->retriedUnits[$index] = true;

        $worker->restart();

        $acquired = $this->budget->acquire();

        assert($acquired);

        try {
            $worker->dispatch($unit);
            // The unit was dispatched successfully once, so its description
            // is known to be transportable; this cannot happen.
            // @codeCoverageIgnoreStart
        } catch (WorkerException $e) {
            $this->budget->release();

            $onCompleted(CompletedWorkUnit::fromCrash($unit, $e->getMessage()));
            // @codeCoverageIgnoreEnd
        }

        return true;
    }

    /**
     * Hand the next queued units to the idle, alive workers.
     *
     * A unit that cannot be dispatched — its description cannot be built or
     * transported — is reported to the
     * callback as a crashed unit and skipped, so that one undispatchable unit
     * does not abort the entire run or starve an otherwise idle worker.
     */
    private function dispatch(): void
    {
        $onCompleted = $this->onCompleted;

        assert($onCompleted !== null);

        foreach ($this->workers as $worker) {
            if (!$worker->isAlive() || $worker->isBusy()) {
                continue;
            }

            while ($this->hasQueuedUnits()) {
                // The idle worker may only be topped up while the shared
                // process budget has a slot left; the slot is held until the
                // dispatched unit finishes.
                if (!$this->budget->acquire()) {
                    return;
                }

                $unit = $this->nextQueuedUnit();

                try {
                    $worker->dispatch($unit);

                    break;
                } catch (WorkerException $e) {
                    // The unit never started executing, so the slot taken for
                    // it goes back to the budget right away.
                    $this->budget->release();

                    $onCompleted(CompletedWorkUnit::fromCrash($unit, $e->getMessage()));
                }
            }
        }
    }

    /**
     * @phpstan-impure
     */
    private function hasQueuedUnits(): bool
    {
        return !$this->queue->isEmpty();
    }

    /**
     * Take the next unit to dispatch, telling the queue what is in flight: the
     * suite indexes of the executing units are what decide whether the queue
     * hands out the next unit in suite order, so that the results keep
     * flowing, or the longest one, so that the chunk finishes as early as
     * possible (see DispatchQueue).
     */
    private function nextQueuedUnit(): WorkUnit
    {
        return $this->queue->next($this->executingIndexes());
    }

    /**
     * The suite indexes of the units that the workers are executing right now.
     *
     * @return list<non-negative-int>
     */
    private function executingIndexes(): array
    {
        $indexes = [];

        foreach ($this->busyWorkers() as $worker) {
            $unit = $worker->currentUnit();

            assert($unit !== null);

            $indexes[] = $unit->index();
        }

        return $indexes;
    }

    private function hasAliveWorkers(): bool
    {
        foreach ($this->workers as $worker) {
            if ($worker->isAlive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<PersistentWorker>
     */
    private function busyWorkers(): array
    {
        $busy = [];

        foreach ($this->workers as $worker) {
            if ($worker->isAlive() && $worker->isBusy()) {
                $busy[] = $worker;
            }
        }

        return $busy;
    }
}
