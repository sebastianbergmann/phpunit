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
use function count;
use function usleep;
use Generator;
use PHPUnit\Event\CollectingEmitter;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use PHPUnit\Util\PHP\Job;
use PHPUnit\Util\PHP\JobRunner;
use PHPUnit\Util\PHP\Result;
use PHPUnit\Util\PHP\RunningJob;

/**
 * Runs PHPT tests concurrently, each as its own child process, in the main
 * PHPUnit process.
 *
 * A PHPT test already runs its --SKIPIF--, --FILE--, and --CLEAN-- sections in
 * child processes; routing it through a PersistentWorker would only wrap those
 * children in a further process, and a child process spawned from within a
 * worker hangs on Windows because it inherits the worker's control-channel
 * handles. This runner therefore drives the PHPT tests directly: it advances up
 * to a fixed number of them at a time, each represented by the generator that
 * PhptTestCase::execute() returns, and starts the next section's child process
 * for a test as soon as the previous one has finished.
 *
 * Because the events of a PHPT test must reach the parent's output and result
 * subsystem in suite order — not in the order in which the concurrently running
 * tests happen to finish — each test writes its events into its own collecting
 * emitter. The collected events are handed to the caller, which replays them at
 * the test's suite index through the same ResultAggregator that orders the
 * worker units.
 *
 * The --FILE-- section's child redirects its standard error onto its standard
 * output, which is captured in a file rather than a pipe; there is therefore no
 * stream to wait on with stream_select(), so the runner polls each child's
 * liveness instead, draining the pipes of the sections that do use them so that
 * a child never blocks on a full pipe buffer.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class PhptRunner
{
    private readonly JobRunner $jobRunner;

    /**
     * @var positive-int
     */
    private readonly int $concurrency;

    /**
     * @param positive-int $concurrency
     */
    public function __construct(JobRunner $jobRunner, int $concurrency)
    {
        $this->jobRunner   = $jobRunner;
        $this->concurrency = $concurrency;
    }

    /**
     * Run the given PHPT units, invoking the callback once for each unit as it
     * finishes (in completion order, not suite order) with its suite index and
     * the events it collected.
     *
     * @param list<PhptWorkUnit>                                $units
     * @param callable(non-negative-int, EventCollection): void $onCompleted
     */
    public function run(array $units, callable $onCompleted): void
    {
        $queue  = $units;
        $active = [];
        $nextId = 0;

        while ($queue !== [] || $active !== []) {
            while (count($active) < $this->concurrency && $queue !== []) {
                $unit      = array_shift($queue);
                $collector = EventFacade::instance()->collectingEmitter();
                $generator = new PhptTestCase($unit->file())->execute($collector->emitter());

                $generator->rewind();

                if (!$generator->valid()) {
                    // The test produced its events without running any child
                    // process — a parse error, or a skip decided in-process.
                    $onCompleted($unit->index(), $collector->flush());

                    continue;
                }

                $active[$nextId] = [
                    'unit'      => $unit,
                    'generator' => $generator,
                    'collector' => $collector,
                    'job'       => $this->jobRunner->startAsync($generator->current()),
                ];

                $nextId++;
            }

            if ($active === []) {
                continue;
            }

            $this->harvest($active, $onCompleted);
        }
    }

    /**
     * @param array<int, array{unit: PhptWorkUnit, generator: Generator<int, Job, Result, void>, collector: CollectingEmitter, job: RunningJob}> $active
     * @param callable(non-negative-int, EventCollection): void                                                                                  $onCompleted
     */
    private function harvest(array &$active, callable $onCompleted): void
    {
        $progressed = false;

        foreach ($active as $id => $task) {
            // Drain whatever the child has produced on its pipes so that it
            // never blocks writing into a full pipe buffer while we wait.
            $task['job']->consume();

            if ($task['job']->isRunning()) {
                continue;
            }

            $progressed = true;

            $generator = $task['generator'];

            $generator->send($task['job']->wait());

            if ($generator->valid()) {
                // The test's next section has to run in a child process too.
                $active[$id]['job'] = $this->jobRunner->startAsync($generator->current());

                continue;
            }

            $onCompleted($task['unit']->index(), $task['collector']->flush());

            unset($active[$id]);
        }

        if (!$progressed) {
            usleep(1000);
        }
    }
}
