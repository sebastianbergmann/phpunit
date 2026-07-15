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
     * @param non-empty-list<PersistentWorker> $workers
     */
    public function __construct(array $workers)
    {
        $this->workers = $workers;
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
        $queue = $units;

        while (true) {
            $this->dispatchTo($queue, $onCompleted);

            $busy = $this->busyWorkers();

            if ($busy === []) {
                break;
            }

            $progressed = false;

            foreach ($busy as $worker) {
                $completed = $worker->poll($onStreamedEvents);

                if ($completed !== null) {
                    $onCompleted($completed);

                    $progressed = true;
                }
            }

            // No worker finished this round: sleep briefly before polling again
            // so that waiting for the workers does not spin the CPU. A worker
            // that did finish is not slept on, so its freed slot is refilled at
            // once on the next iteration.
            if (!$progressed) {
                usleep(self::POLL_INTERVAL_MICROSECONDS);
            }
        }

        // Every worker has died while units remain to be run: account for the
        // abandoned units so that the caller does not silently lose them.
        foreach ($queue as $unit) {
            $onCompleted(new CompletedWorkUnit($unit, '', null, true));
        }
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
     *
     * @param list<WorkUnit>                   $queue
     * @param callable(CompletedWorkUnit):void $onCompleted
     */
    private function dispatchTo(array &$queue, callable $onCompleted): void
    {
        foreach ($this->workers as $worker) {
            if (!$worker->isAlive() || $worker->isBusy()) {
                continue;
            }

            while ($queue !== []) {
                $unit = array_shift($queue);

                try {
                    $worker->dispatch($unit);

                    break;
                } catch (WorkerException $e) {
                    $onCompleted(new CompletedWorkUnit($unit, '', null, true, $e->getMessage()));
                }
            }
        }
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
