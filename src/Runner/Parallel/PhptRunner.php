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
use function assert;
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
    /**
     * How long run() sleeps, in microseconds, when a polling round finds that
     * no child has finished, so that waiting does not spin the CPU.
     */
    private const int POLL_INTERVAL_MICROSECONDS = 1000;
    private readonly JobRunner $jobRunner;

    /**
     * @var positive-int
     */
    private readonly int $concurrency;

    /**
     * The units that have not been started yet, in start order. Skipped
     * positions (units whose conflict keys are currently held) leave holes,
     * so this is keyed by original position rather than being a list.
     *
     * @var array<int, PhptWorkUnit>
     */
    private array $queue = [];

    /**
     * @var array<int, array{unit: PhptWorkUnit, generator: Generator<int, Job, Result, void>, collector: CollectingEmitter, job: RunningJob}>
     */
    private array $active = [];

    /**
     * The conflict keys currently held by a running test, and whether a test
     * that conflicts with "all" is running (which blocks every other test
     * from starting).
     *
     * @var array<non-empty-string, true>
     */
    private array $activeConflicts = [];
    private bool $exclusive        = false;
    private int $nextId            = 0;

    /**
     * @var ?callable(non-negative-int, EventCollection): void
     */
    private $onCompleted;

    /**
     * The budget of concurrently executing units that the PHPT tests share
     * with the worker pool: a slot is taken for every started test and given
     * back when the test finishes, so that the PHPT tests and the worker pool
     * together never execute more units at once than the budget allows.
     */
    private readonly ProcessBudget $budget;

    /**
     * @param positive-int $concurrency
     */
    public function __construct(JobRunner $jobRunner, int $concurrency, ProcessBudget $budget)
    {
        $this->jobRunner   = $jobRunner;
        $this->concurrency = $concurrency;
        $this->budget      = $budget;
    }

    /**
     * Run the given PHPT units to completion, invoking the callback once for
     * each unit as it finishes (in completion order, not suite order) with its
     * suite index and the events it collected.
     *
     * @param list<PhptWorkUnit>                                $units
     * @param callable(non-negative-int, EventCollection): void $onCompleted
     */
    public function run(array $units, callable $onCompleted): void
    {
        $this->begin($units, $onCompleted);

        while (!$this->isFinished()) {
            if (!$this->tick()) {
                usleep(self::POLL_INTERVAL_MICROSECONDS);
            }
        }
    }

    /**
     * Accept the units to run without running them yet: the caller is expected
     * to drive the runner with tick() until isFinished() reports completion.
     * This is what lets the parallel test runner advance the PHPT tests and the
     * worker pool side by side in one polling loop.
     *
     * @param list<PhptWorkUnit>                                $units
     * @param callable(non-negative-int, EventCollection): void $onCompleted
     */
    public function begin(array $units, callable $onCompleted): void
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

        $this->queue           = array_merge($queue, $deferred);
        $this->active          = [];
        $this->activeConflicts = [];
        $this->exclusive       = false;
        $this->nextId          = 0;
        $this->onCompleted     = $onCompleted;
    }

    /**
     * Advance the runner by one polling round: start every queued unit that a
     * free slot and its conflict keys allow, and harvest the children that have
     * finished. Returns whether the round made progress; a caller driving the
     * runner in a loop is expected to sleep briefly when it did not, so that
     * polling does not spin the CPU.
     */
    public function tick(): bool
    {
        $progressed = $this->startRunnable();

        if ($this->active === []) {
            return $progressed;
        }

        if ($this->harvest()) {
            $progressed = true;
        }

        return $progressed;
    }

    /**
     * Whether every unit accepted by begin() has finished.
     */
    public function isFinished(): bool
    {
        return $this->queue === [] && $this->active === [];
    }

    /**
     * Abandon the run: the tests that have not been started yet are dropped
     * and the child processes of the running tests are terminated without
     * waiting for their results. Used when the test runner stops early,
     * because the results collected so far call for it (--stop-on-*).
     */
    public function halt(): void
    {
        $this->queue = [];

        foreach ($this->active as $task) {
            $task['job']->terminate();

            // The slot that the abandoned test held goes back to the shared
            // process budget.
            $this->budget->release();
        }

        $this->active          = [];
        $this->activeConflicts = [];
        $this->exclusive       = false;
    }

    /**
     * Start every queued unit that a free slot and its conflict keys allow.
     */
    private function startRunnable(): bool
    {
        $onCompleted = $this->onCompleted;

        assert($onCompleted !== null);

        $progressed = false;

        foreach ($this->queue as $position => $unit) {
            if (count($this->active) >= $this->concurrency || $this->exclusive) {
                break;
            }

            if (!$this->canStart($unit)) {
                continue;
            }

            // The unit may only start while the shared process budget has a
            // slot left; the slot is held until the test finishes.
            if (!$this->budget->acquire()) {
                break;
            }

            unset($this->queue[$position]);

            $progressed = true;

            $collector = EventFacade::instance()->collectingEmitter();
            $generator = new PhptTestCase($unit->file())->execute($collector->emitter());

            $generator->rewind();

            if (!$generator->valid()) {
                // The test produced its events without running any child
                // process — a parse error, or a skip decided in-process. It
                // is already finished, so it reserves no conflict keys and
                // gives its slot back to the budget right away.
                $this->budget->release();

                $onCompleted($unit->index(), $collector->flush());

                continue;
            }

            $this->reserve($unit);

            $this->active[$this->nextId] = [
                'unit'      => $unit,
                'generator' => $generator,
                'collector' => $collector,
                'job'       => $this->jobRunner->startAsync($generator->current()),
            ];

            $this->nextId++;
        }

        return $progressed;
    }

    /**
     * Whether the unit may be started right now: a unit that conflicts with
     * "all" may start only when nothing else is running, and any other unit may
     * start only when none of its conflict keys are currently held.
     */
    private function canStart(PhptWorkUnit $unit): bool
    {
        if (in_array('all', $unit->conflicts(), true)) {
            return $this->active === [];
        }

        foreach ($unit->conflicts() as $key) {
            if (isset($this->activeConflicts[$key])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Record the conflict keys the unit holds while it runs. The reserved key
     * "all" is tracked through the exclusive flag rather than the key map,
     * because it blocks every other test rather than one sharing its key.
     */
    private function reserve(PhptWorkUnit $unit): void
    {
        foreach ($unit->conflicts() as $key) {
            if ($key === 'all') {
                $this->exclusive = true;

                continue;
            }

            $this->activeConflicts[$key] = true;
        }
    }

    /**
     * Release the conflict keys the unit held once it has finished.
     */
    private function release(PhptWorkUnit $unit): void
    {
        foreach ($unit->conflicts() as $key) {
            if ($key === 'all') {
                $this->exclusive = false;

                continue;
            }

            unset($this->activeConflicts[$key]);
        }
    }

    /**
     * Advance the children of the running tests and finish every test whose
     * last section's child has ended.
     */
    private function harvest(): bool
    {
        $onCompleted = $this->onCompleted;

        assert($onCompleted !== null);

        $progressed = false;

        foreach ($this->active as $id => $task) {
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
                $this->active[$id]['job'] = $this->jobRunner->startAsync($generator->current());

                continue;
            }

            $this->budget->release();

            $onCompleted($task['unit']->index(), $task['collector']->flush());

            $this->release($task['unit']);

            unset($this->active[$id]);
        }

        return $progressed;
    }
}
