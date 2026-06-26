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
use function is_resource;
use function stream_select;

/**
 * A fixed-size pool of PersistentWorkers across which units of work are
 * distributed.
 *
 * Distribution is a dynamic work-stealing queue rather than a static
 * pre-partitioning of the units: whenever a worker becomes idle it pulls the
 * next unit from the queue, which self-balances the load against stragglers.
 *
 * A single thread of control keeps all of the workers busy by multiplexing
 * their control channels with stream_select(): it blocks until at least one
 * worker has reported progress, harvests every worker that is ready, hands the
 * finished units to the caller-supplied callback, and tops the idle workers up
 * with more work until the queue is drained.
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
     * Run all of the given units across the pool, invoking the callback once
     * for each unit as it finishes (in completion order, not suite order).
     *
     * @param list<WorkUnit>                   $units
     * @param callable(CompletedWorkUnit):void $onCompleted
     */
    public function run(array $units, callable $onCompleted): void
    {
        $queue = $units;

        while (true) {
            $this->dispatchTo($queue, $onCompleted);

            $busy = $this->busyWorkers();

            if ($busy === []) {
                break;
            }

            $readable = $this->awaitReadable($busy);

            foreach ($readable as $worker) {
                $completed = $worker->tick();

                if ($completed !== null) {
                    $onCompleted($completed);
                }
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

    /**
     * Block until at least one of the busy workers has produced output on its
     * control channel, then return those workers.
     *
     * @param non-empty-list<PersistentWorker> $busy
     *
     * @return list<PersistentWorker>
     */
    private function awaitReadable(array $busy): array
    {
        $streams = [];
        $byId    = [];

        foreach ($busy as $worker) {
            $stream = $worker->controlChannel();

            if (!is_resource($stream)) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            $streams[] = $stream;
            $byId[]    = $worker;
        }

        if ($streams === []) {
            // @codeCoverageIgnoreStart
            return $busy;
            // @codeCoverageIgnoreEnd
        }

        $write  = null;
        $except = null;

        $ready = @stream_select($streams, $write, $except, 1);

        if ($ready === false || $ready === 0) {
            // @codeCoverageIgnoreStart
            // A signal interrupted the wait, or it timed out: re-examine every
            // busy worker on the next iteration rather than risk missing one.
            return $busy;
            // @codeCoverageIgnoreEnd
        }

        $readable = [];

        foreach ($streams as $stream) {
            foreach ($byId as $index => $worker) {
                if ($worker->controlChannel() === $stream) {
                    $readable[] = $worker;

                    unset($byId[$index]);

                    break;
                }
            }
        }

        return $readable;
    }
}
