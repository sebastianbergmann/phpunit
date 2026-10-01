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
use function spl_object_id;
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
 * callback as a crashed unit. A worker whose process died is booted afresh
 * once another unit is to be dispatched to it, so that the units that come
 * after a crashed one run as they would have run without the crash.
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
     * The number of units a worker runs before it is replaced by a fresh
     * process, or 0 when a worker is never replaced.
     *
     * A worker accumulates whatever the tests it runs leave behind in the
     * process — static state, caches, leaked objects — the way a sequential
     * run does. Replacing the worker bounds that accumulation. The
     * replacement is a planned restart, not a crash: the worker is shut down
     * gracefully once it is idle, so its output is harvested as it is at the
     * end of the run, and the fresh process keeps the worker's ordinal
     * identity. Nothing is replaced while no unit is queued for the fresh
     * process to run, as the run is then about to shut every worker down.
     *
     * @var non-negative-int
     */
    private readonly int $numberOfUnitsBeforeRecycling;

    /**
     * How many units each worker has completed since it was last started,
     * keyed by the worker's object id.
     *
     * @var array<int, non-negative-int>
     */
    private array $completedUnits = [];

    /**
     * Whether the units that are executing were asked to halt (see halt()):
     * the pool then only waits for them, and discards their results.
     */
    private bool $halting = false;

    /**
     * @param non-empty-list<PersistentWorker> $workers
     * @param non-negative-int                 $numberOfUnitsBeforeRecycling
     */
    public function __construct(array $workers, ProcessBudget $budget, int $numberOfUnitsBeforeRecycling = 0)
    {
        $this->workers                      = $workers;
        $this->budget                       = $budget;
        $this->numberOfUnitsBeforeRecycling = $numberOfUnitsBeforeRecycling;
        $this->queue                        = new DispatchQueue([], self::SUITE_ORDER_SLOTS);
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
        $this->halting            = false;
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
     *
     * Once halt() has been called, a round only harvests the workers whose
     * units have halted.
     */
    public function tick(bool $mayDispatch = true): bool
    {
        if ($this->halting) {
            return $this->harvestHaltedUnits();
        }

        $onCompleted      = $this->onCompleted;
        $onStreamedEvents = $this->onStreamedEvents;

        assert($onCompleted !== null);
        assert($onStreamedEvents !== null);

        if ($mayDispatch) {
            $this->dispatch();
        }

        $busy = $this->busyWorkers();

        // With no unit in flight, queued units are waiting for a slot of the
        // shared process budget, which units executing elsewhere hold, or for
        // the caller to allow dispatching again.
        if ($busy === []) {
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

            $this->recycleWhenDue($worker);
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
     * Abandon the units that are queued and the units that are executing,
     * because the time limit for the test run has been exceeded: the queued
     * units are dropped, as halt() drops them, and the executing units are
     * terminated and handed to the completion callback as aborted, so that
     * the test that each of them was running is reported as aborted with the
     * given message, as the sequential test runner reports the test that it
     * aborts.
     *
     * @param non-empty-string $message
     */
    public function abort(string $message): void
    {
        $onCompleted      = $this->onCompleted;
        $onStreamedEvents = $this->onStreamedEvents;

        assert($onCompleted !== null);
        assert($onStreamedEvents !== null);

        $this->queue->clear();

        foreach ($this->workers as $worker) {
            if (!$worker->isAlive() || !$worker->isBusy()) {
                continue;
            }

            $unit = $worker->abort($onStreamedEvents);

            // The slot that the aborted unit held goes back to the shared
            // process budget.
            $this->budget->release();

            $onCompleted(CompletedWorkUnit::fromAbortionByTimeLimit($unit, $message));
        }
    }

    /**
     * Abandon the run, because the results collected so far call for the
     * test runner to stop (--stop-on-*): the units that have not been
     * dispatched yet are dropped, and every worker that is busy executing a
     * unit is asked to halt it as the sequential test runner stops — the test
     * that is running finishes, no further test of the unit is started, and
     * the methods that run after the last test of the class, such as
     * tearDownAfterClass(), are run, so that the unit does not leave its
     * fixtures behind.
     *
     * The caller is expected to keep driving the pool with tick() until no
     * unit is executing anymore. The results of the halted units, and the
     * events that their workers stream until then, are discarded: they are
     * for tests that a sequential run would not have run. The workers stay
     * alive and are shut down by stop() as usual.
     */
    public function halt(): void
    {
        $this->queue->clear();

        $this->halting = true;

        foreach ($this->busyWorkers() as $worker) {
            $worker->requestHalt();
        }
    }

    /**
     * Abandon the run without waiting for the units that are executing: the
     * units that have not been dispatched yet are dropped, and every worker
     * that is busy executing a unit is terminated without waiting for its
     * result. Used when the deadline of a time limit for the test run passes
     * while the test runner waits for the units that it asked to halt; the
     * workers that are idle stay alive and are shut down by stop() as usual.
     */
    public function kill(): void
    {
        $this->queue->clear();

        foreach ($this->busyWorkers() as $worker) {
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

        $this->restart($worker);

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
     * Count the unit the worker has just completed and, once the worker has
     * completed as many as the recycling limit allows, replace it with a
     * fresh process — provided it is still alive (a worker that died with its
     * unit is dealt with by the retry) and there is a queued unit for the
     * fresh process to run.
     *
     * @throws WorkerException
     */
    private function recycleWhenDue(PersistentWorker $worker): void
    {
        if ($this->numberOfUnitsBeforeRecycling === 0 || !$worker->isAlive()) {
            return;
        }

        $id = spl_object_id($worker);

        if (!isset($this->completedUnits[$id])) {
            $this->completedUnits[$id] = 0;
        }

        $this->completedUnits[$id]++;

        if ($this->completedUnits[$id] < $this->numberOfUnitsBeforeRecycling || !$this->hasQueuedUnits()) {
            return;
        }

        $worker->stop();
        $worker->restart();

        unset($this->completedUnits[$id]);
    }

    /**
     * Hand the next queued units to the workers that are not busy, booting a
     * fresh process for a worker whose process has died.
     *
     * A unit that cannot be dispatched — its description cannot be built or
     * transported, or no fresh worker process can be booted for it — is
     * reported to the callback as a crashed unit and skipped, so that one
     * undispatchable unit does not abort the entire run or starve an
     * otherwise idle worker.
     */
    private function dispatch(): void
    {
        $onCompleted = $this->onCompleted;

        assert($onCompleted !== null);

        foreach ($this->workers as $worker) {
            if ($worker->isBusy()) {
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
                    // A worker whose process died while it was running an
                    // earlier unit, and that was not booted afresh to retry
                    // that unit, is booted afresh for this one.
                    if (!$worker->isAlive()) {
                        $this->restart($worker);
                    }

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

    /**
     * Boot a fresh worker process in place of one that has died. The fresh
     * process has not completed any unit yet, so its count towards recycling
     * starts over.
     *
     * @throws WorkerException
     */
    private function restart(PersistentWorker $worker): void
    {
        $worker->restart();

        unset($this->completedUnits[spl_object_id($worker)]);
    }

    /**
     * Harvest every worker that has halted the unit it was asked to halt.
     * The unit's result is discarded, and so are the events that the worker
     * streams in the meantime.
     */
    private function harvestHaltedUnits(): bool
    {
        $progressed = false;

        foreach ($this->busyWorkers() as $worker) {
            $completed = $worker->poll(
                static function (WorkUnit $unit, EventCollection $events): void
                {
                },
            );

            if ($completed === null) {
                continue;
            }

            $progressed = true;

            // The slot that the halted unit held goes back to the shared
            // process budget.
            $this->budget->release();
        }

        return $progressed;
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
