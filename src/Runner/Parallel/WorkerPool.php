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

use function array_shift;
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
 * A single thread of control keeps all of the workers busy by polling them in
 * rounds: each round it drains the events that the busy workers have streamed
 * so far, harvests every worker that has finished its unit, hands both to the
 * caller-supplied callbacks, tops the idle workers up with more work, and
 * sleeps briefly before the next round if none finished.
 * A worker signals completion through the filesystem (see PersistentWorker),
 * which is polled rather than waited on with stream_select() because the latter
 * does not work on the workers' output pipes on Windows.
 *
 * If a worker dies, the unit it was running is reported to the callback as a
 * crashed unit and the dead worker is dropped from the pool; the remaining
 * units are redistributed across the surviving workers. Should every worker
 * die, the units that were never started are likewise reported as crashed so
 * that the caller can account for all of them.
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
     * @var non-empty-list<PersistentWorker>
     */
    private readonly array $workers;

    /**
     * The units that have not been dispatched to a worker yet, in dispatch
     * order.
     *
     * @var list<WorkUnit>
     */
    private array $queue = [];

    /**
     * @var ?callable(CompletedWorkUnit):void
     */
    private $onCompleted;

    /**
     * @var ?callable(WorkUnit, EventCollection):void
     */
    private $onStreamedEvents;

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
     */
    public function run(array $units, callable $onCompleted, callable $onStreamedEvents): void
    {
        $this->begin($units, $onCompleted, $onStreamedEvents);

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
     */
    public function begin(array $units, callable $onCompleted, callable $onStreamedEvents): void
    {
        $this->queue            = $units;
        $this->onCompleted      = $onCompleted;
        $this->onStreamedEvents = $onStreamedEvents;
    }

    /**
     * Advance the pool by one polling round: top the idle workers up with
     * queued units, drain the events the busy workers have streamed, and
     * harvest every worker that has finished its unit. Returns whether the
     * round made progress; a caller driving the pool in a loop is expected to
     * sleep briefly when it did not, so that polling does not spin the CPU.
     */
    public function tick(): bool
    {
        $onCompleted      = $this->onCompleted;
        $onStreamedEvents = $this->onStreamedEvents;

        assert($onCompleted !== null);
        assert($onStreamedEvents !== null);

        $this->dispatch();

        $busy = $this->busyWorkers();

        if ($busy === []) {
            // With no unit in flight, queued units can be waiting for one of
            // two reasons: every worker has died, or the shared process budget
            // is exhausted by units executing elsewhere. Only the former is
            // terminal; the abandoned units are then accounted for so that the
            // caller does not silently lose them.
            if ($this->queue !== [] && !$this->hasAliveWorkers()) {
                foreach ($this->queue as $unit) {
                    $onCompleted(new CompletedWorkUnit($unit, '', null, true));
                }

                $this->queue = [];

                return true;
            }

            return false;
        }

        $progressed = false;

        foreach ($busy as $worker) {
            $completed = $worker->poll($onStreamedEvents);

            if ($completed !== null) {
                $this->budget->release();

                $onCompleted($completed);

                $progressed = true;
            }
        }

        return $progressed;
    }

    /**
     * Whether every unit accepted by begin() has finished.
     */
    public function isFinished(): bool
    {
        return $this->queue === [] && $this->busyWorkers() === [];
    }

    public function stop(): void
    {
        foreach ($this->workers as $worker) {
            $worker->stop();
        }
    }

    /**
     * Hand the next queued units to the idle, alive workers.
     *
     * A unit that cannot be dispatched — for instance because its test data
     * cannot be serialized for transport to a worker — is reported to the
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

            while ($this->queue !== []) {
                // The idle worker may only be topped up while the shared
                // process budget has a slot left; the slot is held until the
                // dispatched unit finishes.
                if (!$this->budget->acquire()) {
                    return;
                }

                $unit = array_shift($this->queue);

                try {
                    $worker->dispatch($unit);

                    break;
                } catch (WorkerException $e) {
                    // The unit never started executing, so the slot taken for
                    // it goes back to the budget right away.
                    $this->budget->release();

                    $onCompleted(new CompletedWorkUnit($unit, '', null, true, $e->getMessage()));
                }
            }
        }
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
