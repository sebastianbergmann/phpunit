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

use function array_keys;
use function ksort;
use function sys_get_temp_dir;
use function unlink;
use function usleep;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestRunner\ChildProcessResultProcessor;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestRunner\TestResult\PassedTests;
use PHPUnit\Util\PHP\Job;
use PHPUnit\Util\PHP\JobRunner;

#[CoversClass(PhptRunner::class)]
#[CoversClass(PhptWorkUnit::class)]
#[UsesClass(JobRunner::class)]
#[UsesClass(Job::class)]
#[Large]
final class PhptRunnerTest extends TestCase
{
    public function testRunsPhptTestsConcurrentlyAndReportsEachWithItsCollectedEvents(): void
    {
        $file = __DIR__ . '/../../../_files/parallel-worker/worker.phpt';

        $units = [
            new PhptWorkUnit(0, $file),
            new PhptWorkUnit(1, $file),
        ];

        $collected = $this->execute($units, 2);

        $this->assertSame([0, 1], array_keys($collected));

        foreach ($collected as $events) {
            $this->assertTrue($this->contains($events, Passed::class));
        }
    }

    public function testRunsAPhptTestWhoseSectionsEachNeedTheirOwnChildProcess(): void
    {
        // The --INI-- section forces the --CLEAN-- section to run in a child
        // process of its own, so this test's generator yields a second job
        // after the --FILE-- job and exercises the runner's handling of a unit
        // that is not finished by its first child process.
        $units = [
            new PhptWorkUnit(0, __DIR__ . '/../../../_files/parallel-worker/worker-with-clean.phpt'),
        ];

        $collected = $this->execute($units, 2);

        $this->assertSame([0], array_keys($collected));
        $this->assertTrue($this->contains($collected[0], Passed::class));
    }

    public function testReportsAPhptTestThatNeedsNoChildProcessAtAll(): void
    {
        // This test is skipped by a --SKIPIF-- section that runs in-process, so
        // its generator produces its events without ever yielding a job; the
        // runner must report it just the same.
        $units = [
            new PhptWorkUnit(0, __DIR__ . '/../../../_files/parallel-worker/worker-skipped.phpt'),
        ];

        $collected = $this->execute($units, 2);

        $this->assertSame([0], array_keys($collected));
        $this->assertTrue($this->contains($collected[0], Skipped::class));
    }

    public function testRunsPhptTestsOneAtATimeWhenConcurrencyIsOne(): void
    {
        $file = __DIR__ . '/../../../_files/parallel-worker/worker.phpt';

        $units = [
            new PhptWorkUnit(0, $file),
            new PhptWorkUnit(1, $file),
            new PhptWorkUnit(2, $file),
        ];

        $collected = $this->execute($units, 1);

        $this->assertSame([0, 1, 2], array_keys($collected));

        foreach ($collected as $events) {
            $this->assertTrue($this->contains($events, Passed::class));
        }
    }

    public function testHaltDropsTheQueuedTestsAndTerminatesTheRunningChildProcesses(): void
    {
        $budget = new ProcessBudget(1);

        $runner = $this->runner(1, $budget);

        $collected = [];

        $runner->begin(
            [
                new PhptWorkUnit(0, __DIR__ . '/../../../_files/parallel-worker/worker-sleeping.phpt'),
                new PhptWorkUnit(1, __DIR__ . '/../../../_files/parallel-worker/worker.phpt'),
            ],
            static function (int $index, EventCollection $events) use (&$collected): void
            {
                $collected[$index] = $events;
            },
        );

        $runner->tick();

        $this->assertFalse($runner->isFinished());

        $runner->halt();

        // The queued test was dropped and the sleeping test's child process
        // was terminated without being waited for: the runner is finished,
        // nothing was reported, and the slot the terminated test held has
        // been given back to the shared budget.
        $this->assertTrue($runner->isFinished());
        $this->assertSame([], $collected);
        $this->assertTrue($budget->acquire());
    }

    public function testDoesNotStartATestWhenTheCallerDisallowsIt(): void
    {
        $budget = new ProcessBudget(1);

        $runner = $this->runner(1, $budget);

        $collected = [];

        $runner->begin(
            [
                new PhptWorkUnit(0, __DIR__ . '/../../../_files/parallel-worker/worker.phpt'),
            ],
            static function (int $index, EventCollection $events) use (&$collected): void
            {
                $collected[$index] = $events;
            },
        );

        // The caller makes room for a unit that must run alone: no queued
        // test is started, so the runner drains instead of topping up.
        $this->assertFalse($runner->tick(false));
        $this->assertFalse($runner->hasRunningTests());
        $this->assertFalse($runner->isFinished());
        $this->assertSame([], $collected);

        while (!$runner->isFinished()) {
            if (!$runner->tick()) {
                usleep(1000);
            }
        }

        $this->assertCount(1, $collected);
    }

    public function testStartsATestThatConflictsWithAllOnlyWhenNothingElseIsExecuting(): void
    {
        $nothingElseIsExecuting = false;

        $budget = new ProcessBudget(2);

        $runner = $this->runner(
            2,
            $budget,
            static function () use (&$nothingElseIsExecuting): bool
            {
                return $nothingElseIsExecuting;
            },
        );

        $collected = [];

        $runner->begin(
            [
                new PhptWorkUnit(0, __DIR__ . '/../../../_files/parallel-worker/worker.phpt'),
                new PhptWorkUnit(1, __DIR__ . '/../../../_files/parallel-worker/worker.phpt', ['all']),
            ],
            static function (int $index, EventCollection $events) use (&$collected): void
            {
                $collected[$index] = $events;
            },
        );

        // While another PHPT test is running, the test that must run
        // entirely on its own is not started.
        $runner->tick();

        $this->assertTrue($runner->hasRunningTests());
        $this->assertFalse($runner->isRunningExclusiveTest());

        // The other test has finished, but a unit is still executing
        // elsewhere — in the worker pool, in a real run — so the test that
        // must run entirely on its own is still not started.
        while ($collected === []) {
            if (!$runner->tick()) {
                usleep(1000);
            }
        }

        $runner->tick();

        $this->assertFalse($runner->hasRunningTests());
        $this->assertFalse($runner->isFinished());

        // Nothing is executing anywhere anymore: now the test starts, and it
        // is the only one running.
        $nothingElseIsExecuting = true;

        $runner->tick();

        $this->assertTrue($runner->hasRunningTests());
        $this->assertTrue($runner->isRunningExclusiveTest());

        while (!$runner->isFinished()) {
            if (!$runner->tick()) {
                usleep(1000);
            }
        }

        $this->assertFalse($runner->isRunningExclusiveTest());
        $this->assertCount(2, $collected);
    }

    public function testRunsTheCleanSectionOfATerminatedTestWhenHalting(): void
    {
        $marker = sys_get_temp_dir() . '/phpunit-parallel-halt-clean.marker';

        @unlink($marker);

        $budget = new ProcessBudget(1);

        $runner = $this->runner(1, $budget);

        $collected = [];

        $runner->begin(
            [
                new PhptWorkUnit(0, __DIR__ . '/../../../_files/parallel-worker/worker-sleeping-with-clean.phpt'),
            ],
            static function (int $index, EventCollection $events) use (&$collected): void
            {
                $collected[$index] = $events;
            },
        );

        $runner->tick();

        $runner->halt();

        // The terminated test's --CLEAN-- section ran even though the test
        // itself was abandoned mid-sleep and nothing was reported for it.
        $this->assertFileExists($marker);
        $this->assertSame([], $collected);

        @unlink($marker);
    }

    public function testWaitsForASlotOfTheSharedProcessBudgetBeforeStartingATest(): void
    {
        $budget = new ProcessBudget(1);

        $runner = $this->runner(2, $budget);

        $collected = [];

        $runner->begin(
            [new PhptWorkUnit(0, __DIR__ . '/../../../_files/parallel-worker/worker.phpt')],
            static function (int $index, EventCollection $events) use (&$collected): void
            {
                $collected[$index] = $events;
            },
        );

        // The budget's only slot is held by a unit executing elsewhere — a
        // test class running in a worker, in a real run. The runner must not
        // start the test until the slot has been given back.
        $this->assertTrue($budget->acquire());

        $this->assertFalse($runner->tick());
        $this->assertFalse($runner->isFinished());
        $this->assertSame([], $collected);

        $budget->release();

        while (!$runner->isFinished()) {
            if (!$runner->tick()) {
                usleep(1000);
            }
        }

        $this->assertSame([0], array_keys($collected));
        $this->assertTrue($this->contains($collected[0], Passed::class));
    }

    /**
     * @param list<PhptWorkUnit> $units
     * @param positive-int       $concurrency
     *
     * @return array<non-negative-int, EventCollection>
     */
    private function execute(array $units, int $concurrency): array
    {
        $collected = [];

        $this->runner($concurrency, new ProcessBudget($concurrency))->run(
            $units,
            static function (int $index, EventCollection $events) use (&$collected): void
            {
                $collected[$index] = $events;
            },
        );

        ksort($collected);

        return $collected;
    }

    /**
     * @param positive-int $concurrency
     */
    private function runner(int $concurrency, ProcessBudget $budget, ?callable $nothingElseIsExecuting = null): PhptRunner
    {
        $processor = new ChildProcessResultProcessor(
            Facade::instance(),
            $this->createStub(Emitter::class),
            new PassedTests,
            new CodeCoverage,
        );

        return new PhptRunner(new JobRunner($processor), $concurrency, $budget, $nothingElseIsExecuting);
    }

    /**
     * @param class-string $eventClass
     */
    private function contains(EventCollection $events, string $eventClass): bool
    {
        foreach ($events as $event) {
            if ($event instanceof $eventClass) {
                return true;
            }
        }

        return false;
    }
}
