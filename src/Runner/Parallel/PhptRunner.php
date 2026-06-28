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

use function array_merge;
use function count;
use function in_array;
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
 * A test may declare conflict keys with a --CONFLICTS-- section: while a test
 * that conflicts with key K is running, no other test that conflicts with K is
 * started. The reserved key "all" conflicts with every other test, so a test
 * that declares it runs entirely on its own; such tests are ordered last and
 * run once the others have drained, mirroring how run-tests.php defers them
 * until a single worker remains.
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
        // Tests that conflict with "all" run on their own; ordering them last
        // lets the others start first, so an "all" test only runs once the
        // queue ahead of it has drained.
        $queue    = [];
        $deferred = [];

        foreach ($units as $unit) {
            if (in_array('all', $unit->conflicts(), true)) {
                $deferred[] = $unit;

                continue;
            }

            $queue[] = $unit;
        }

        $queue = array_merge($queue, $deferred);

        $active = [];

        // The conflict keys currently held by a running test, and whether a
        // test that conflicts with "all" is running (which blocks every other
        // test from starting).
        $activeConflicts = [];
        $exclusive       = false;
        $nextId          = 0;

        while ($queue !== [] || $active !== []) {
            foreach ($queue as $position => $unit) {
                if (count($active) >= $this->concurrency || $exclusive) {
                    break;
                }

                if (!$this->canStart($unit, $active, $activeConflicts)) {
                    continue;
                }

                unset($queue[$position]);

                $collector = EventFacade::instance()->collectingEmitter();
                $generator = new PhptTestCase($unit->file())->execute($collector->emitter());

                $generator->rewind();

                if (!$generator->valid()) {
                    // The test produced its events without running any child
                    // process — a parse error, or a skip decided in-process. It
                    // is already finished, so it reserves no conflict keys.
                    $onCompleted($unit->index(), $collector->flush());

                    continue;
                }

                $this->reserve($unit, $activeConflicts, $exclusive);

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

            $this->harvest($active, $onCompleted, $activeConflicts, $exclusive);
        }
    }

    /**
     * Whether the unit may be started right now: a unit that conflicts with
     * "all" may start only when nothing else is running, and any other unit may
     * start only when none of its conflict keys are currently held.
     *
     * @param array<int, array{unit: PhptWorkUnit, generator: Generator<int, Job, Result, void>, collector: CollectingEmitter, job: RunningJob}> $active
     * @param array<non-empty-string, true>                                                                                                      $activeConflicts
     */
    private function canStart(PhptWorkUnit $unit, array $active, array $activeConflicts): bool
    {
        if (in_array('all', $unit->conflicts(), true)) {
            return $active === [];
        }

        foreach ($unit->conflicts() as $key) {
            if (isset($activeConflicts[$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Record the conflict keys the unit holds while it runs. The reserved key
     * "all" is tracked through the exclusive flag rather than the key map,
     * because it blocks every other test rather than one sharing its key.
     *
     * @param array<non-empty-string, true> $activeConflicts
     */
    private function reserve(PhptWorkUnit $unit, array &$activeConflicts, bool &$exclusive): void
    {
        foreach ($unit->conflicts() as $key) {
            if ($key === 'all') {
                $exclusive = true;

                continue;
            }

            $activeConflicts[$key] = true;
        }
    }

    /**
     * Release the conflict keys the unit held once it has finished.
     *
     * @param array<non-empty-string, true> $activeConflicts
     */
    private function release(PhptWorkUnit $unit, array &$activeConflicts, bool &$exclusive): void
    {
        foreach ($unit->conflicts() as $key) {
            if ($key === 'all') {
                $exclusive = false;

                continue;
            }

            unset($activeConflicts[$key]);
        }
    }

    /**
     * @param array<int, array{unit: PhptWorkUnit, generator: Generator<int, Job, Result, void>, collector: CollectingEmitter, job: RunningJob}> $active
     * @param callable(non-negative-int, EventCollection): void                                                                                  $onCompleted
     * @param array<non-empty-string, true>                                                                                                      $activeConflicts
     */
    private function harvest(array &$active, callable $onCompleted, array &$activeConflicts, bool &$exclusive): void
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

            $this->release($task['unit'], $activeConflicts, $exclusive);

            unset($active[$id]);
        }

        if (!$progressed) {
            usleep(1000);
        }
    }
}
