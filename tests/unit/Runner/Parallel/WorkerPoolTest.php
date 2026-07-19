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

use function sort;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;
use function usleep;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestRunner\ChildProcessResultProcessor;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestFixture\ParallelWorker\WorkerCrashesOnceTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerSecondTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerSleepingTest;
use PHPUnit\TestRunner\TestResult\PassedTests;
use PHPUnit\Util\PHP\Job;
use PHPUnit\Util\PHP\JobRunner;

#[CoversClass(WorkerPool::class)]
#[CoversClass(TestClassWorkUnit::class)]
#[CoversClass(CompletedWorkUnit::class)]
#[CoversClass(PersistentWorker::class)]
#[UsesClass(JobRunner::class)]
#[UsesClass(Job::class)]
#[Large]
final class WorkerPoolTest extends TestCase
{
    public function testRunsUnitsAcrossWorkersAndReportsEachAsCompleted(): void
    {
        $units = [
            new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
            new TestClassWorkUnit(1, WorkerSecondTest::class, [new WorkerSecondTest('testThatFails')]),
        ];

        $streamed = [];

        $completed = $this->execute($this->pool(2), $units, $streamed);

        $this->assertCount(2, $completed);

        foreach ($completed as $unit) {
            $this->assertFalse($unit->crashed());
            $this->assertNotSame('', $unit->serializedResult());
            $this->assertNotNull($unit->nonce());
        }

        $this->assertSame([0, 1], $this->indexesOf($completed));

        // Both units streamed the events of their finished tests while they
        // were still running.
        $this->assertArrayHasKey(0, $streamed);
        $this->assertArrayHasKey(1, $streamed);
    }

    public function testReportsACrashedUnitWhenItsWorkerDies(): void
    {
        $units = [
            new TestClassWorkUnit(0, WorkerSecondTest::class, [new WorkerSecondTest('testThatKillsTheWorkerProcess')]),
        ];

        $completed = $this->execute($this->pool(1), $units);

        $this->assertCount(1, $completed);
        $this->assertTrue($completed[0]->crashed());
    }

    public function testReportsRemainingUnitsAsCrashedWhenEveryWorkerHasDied(): void
    {
        // The only worker dies on the first unit, so the second unit can no
        // longer be dispatched anywhere; it must still be reported as crashed
        // rather than silently lost.
        $units = [
            new TestClassWorkUnit(0, WorkerSecondTest::class, [new WorkerSecondTest('testThatKillsTheWorkerProcess')]),
            new TestClassWorkUnit(1, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
        ];

        $completed = $this->execute($this->pool(1), $units);

        $this->assertCount(2, $completed);
        $this->assertSame([0, 1], $this->indexesOf($completed));

        foreach ($completed as $unit) {
            $this->assertTrue($unit->crashed());
        }
    }

    public function testReportsAUnitWhoseDataCannotBeSerializedAsCrashedAndKeepsRunning(): void
    {
        $test = new WorkerFirstTest('testStartsTheProcessLocalCounter');
        $test->setData('with-closure', [static fn (): null => null]);

        $units = [
            new TestClassWorkUnit(0, WorkerFirstTest::class, [$test]),
            new TestClassWorkUnit(1, WorkerSecondTest::class, [new WorkerSecondTest('testThatFails')]),
        ];

        $completed = $this->execute($this->pool(1), $units);

        $this->assertCount(2, $completed);

        $byIndex = [];

        foreach ($completed as $unit) {
            $byIndex[$unit->unit()->index()] = $unit;
        }

        $this->assertTrue($byIndex[0]->crashed());
        $this->assertNotNull($byIndex[0]->message());
        $this->assertStringContainsString('cannot be serialized', (string) $byIndex[0]->message());

        // The unit that could be serialized still ran on the same worker.
        $this->assertFalse($byIndex[1]->crashed());
    }

    public function testRedistributesRemainingUnitsAcrossSurvivingWorkersWhenAWorkerDies(): void
    {
        $units = [
            new TestClassWorkUnit(0, WorkerSecondTest::class, [new WorkerSecondTest('testThatKillsTheWorkerProcess')]),
            new TestClassWorkUnit(1, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
            new TestClassWorkUnit(2, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
        ];

        $completed = $this->execute($this->pool(2), $units);

        $this->assertCount(3, $completed);
        $this->assertSame([0, 1, 2], $this->indexesOf($completed));

        $crashed = [];

        foreach ($completed as $unit) {
            $crashed[$unit->unit()->index()] = $unit->crashed();
        }

        $this->assertTrue($crashed[0]);
        $this->assertFalse($crashed[1]);
        $this->assertFalse($crashed[2]);
    }

    public function testRetriesAUnitWhoseWorkerDiedOnceOnAFreshWorker(): void
    {
        $marker = tempnam(sys_get_temp_dir(), 'phpunit_crash_once_');

        $this->assertNotFalse($marker);

        // The marker must not exist yet: its absence is what makes the
        // fixture test kill its worker on the first attempt.
        unlink($marker);

        $test = new WorkerCrashesOnceTest('testThatCrashesOnTheFirstAttempt');
        $test->setData('marker', [$marker]);

        $units = [
            new TestClassWorkUnit(0, WorkerCrashesOnceTest::class, [$test]),
        ];

        $completed = $this->execute($this->pool(1), $units);

        @unlink($marker);

        // The first attempt killed the worker; the retry, on a freshly booted
        // worker, passed — so the unit is reported as completed, not crashed.
        $this->assertCount(1, $completed);
        $this->assertFalse($completed[0]->crashed());
    }

    public function testDoesNotRetryAUnitWhoseRetryWasVetoed(): void
    {
        $units = [
            new TestClassWorkUnit(0, WorkerSecondTest::class, [new WorkerSecondTest('testThatKillsTheWorkerProcess')]),
        ];

        $vetoed   = [];
        $streamed = [];

        $completed = $this->execute(
            $this->pool(1),
            $units,
            $streamed,
            static function (WorkUnit $unit) use (&$vetoed): bool
            {
                $vetoed[] = $unit->index();

                return false;
            },
        );

        // The caller vetoed the retry — some of the unit's results had
        // already been reported, in a real run — so the unit is reported as
        // crashed after its first attempt.
        $this->assertSame([0], $vetoed);
        $this->assertCount(1, $completed);
        $this->assertTrue($completed[0]->crashed());
    }

    public function testDoesNotDispatchWhenTheCallerDisallowsIt(): void
    {
        $budget = new ProcessBudget(1);

        $pool = $this->pool(1, $budget);

        $pool->start();

        try {
            $completed = [];

            $pool->begin(
                [new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')])],
                static function (CompletedWorkUnit $unit) use (&$completed): void
                {
                    $completed[] = $unit;
                },
                static function (WorkUnit $unit, EventCollection $events): void
                {
                },
                static function (WorkUnit $unit): bool
                {
                    return true;
                },
            );

            // The caller makes room for a unit that must run alone: no queued
            // unit is dispatched, so the pool drains instead of topping up.
            $this->assertFalse($pool->tick(false));
            $this->assertFalse($pool->hasExecutingUnits());
            $this->assertFalse($pool->isFinished());
            $this->assertSame([], $completed);

            // Dispatching is allowed again; the queued unit is dispatched and
            // runs to completion.
            $pool->tick();

            $this->assertTrue($pool->hasExecutingUnits());

            while (!$pool->isFinished()) {
                if (!$pool->tick()) {
                    usleep(1000);
                }
            }

            $this->assertFalse($pool->hasExecutingUnits());
            $this->assertCount(1, $completed);
        } finally {
            $pool->stop();
        }
    }

    public function testWaitsForASlotOfTheSharedProcessBudgetBeforeDispatching(): void
    {
        $budget = new ProcessBudget(1);

        $pool = $this->pool(1, $budget);

        $pool->start();

        try {
            $completed = [];

            $pool->begin(
                [new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')])],
                static function (CompletedWorkUnit $unit) use (&$completed): void
                {
                    $completed[] = $unit;
                },
                static function (WorkUnit $unit, EventCollection $events): void
                {
                },
                static function (WorkUnit $unit): bool
                {
                    return true;
                },
            );

            // The budget's only slot is held by a unit executing elsewhere —
            // a PHPT test, in a real run. The pool must not dispatch, and it
            // must not mistake the starvation for a pool whose workers have
            // all died, which would report the queued unit as crashed.
            $this->assertTrue($budget->acquire());

            $this->assertFalse($pool->tick());
            $this->assertFalse($pool->isFinished());
            $this->assertSame([], $completed);

            // The slot has been given back; the pool may dispatch now.
            $budget->release();

            while (!$pool->isFinished()) {
                if (!$pool->tick()) {
                    usleep(1000);
                }
            }

            $this->assertCount(1, $completed);
            $this->assertFalse($completed[0]->crashed());
        } finally {
            $pool->stop();
        }
    }

    public function testHaltDropsTheQueuedUnitsAndTerminatesTheBusyWorkers(): void
    {
        $budget = new ProcessBudget(1);

        $pool = $this->pool(1, $budget);

        $pool->start();

        try {
            $completed = [];

            $pool->begin(
                [
                    new TestClassWorkUnit(0, WorkerSleepingTest::class, [new WorkerSleepingTest('testThatSleeps')]),
                    new TestClassWorkUnit(1, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
                ],
                static function (CompletedWorkUnit $unit) use (&$completed): void
                {
                    $completed[] = $unit;
                },
                static function (WorkUnit $unit, EventCollection $events): void
                {
                },
                static function (WorkUnit $unit): bool
                {
                    return true;
                },
            );

            $pool->tick();

            $this->assertFalse($pool->isFinished());

            $pool->halt();

            // The queued unit was dropped and the sleeping unit's worker was
            // terminated without being waited for: the pool is finished,
            // nothing was reported, and the slot the terminated unit held has
            // been given back to the shared budget.
            $this->assertTrue($pool->isFinished());
            $this->assertSame([], $completed);
            $this->assertTrue($budget->acquire());
        } finally {
            $pool->stop();
        }
    }

    public function testATickOnAFinishedPoolReportsNoProgress(): void
    {
        $pool = $this->pool(1);

        $pool->begin(
            [],
            static function (CompletedWorkUnit $unit): void
            {
            },
            static function (WorkUnit $unit, EventCollection $events): void
            {
            },
            static function (WorkUnit $unit): bool
            {
                return true;
            },
        );

        // The parallel test runner advances the pool and the PHPT runner side
        // by side and keeps ticking both until both are finished, so a tick on
        // a pool that has nothing left to do must be a harmless no-op.
        $this->assertTrue($pool->isFinished());
        $this->assertFalse($pool->tick());
    }

    /**
     * @param list<WorkUnit>                                 $units
     * @param array<non-negative-int, list<EventCollection>> $streamed
     *
     * @return list<CompletedWorkUnit>
     */
    private function execute(WorkerPool $pool, array $units, array &$streamed = [], ?callable $onCrashedUnitRetry = null): array
    {
        if ($onCrashedUnitRetry === null) {
            $onCrashedUnitRetry = static function (WorkUnit $unit): bool
            {
                return true;
            };
        }

        $completed = [];

        $pool->start();

        try {
            $pool->run(
                $units,
                static function (CompletedWorkUnit $unit) use (&$completed): void
                {
                    $completed[] = $unit;
                },
                static function (WorkUnit $unit, EventCollection $events) use (&$streamed): void
                {
                    if (!isset($streamed[$unit->index()])) {
                        $streamed[$unit->index()] = [];
                    }

                    $streamed[$unit->index()][] = $events;
                },
                $onCrashedUnitRetry,
            );
        } finally {
            $pool->stop();
        }

        return $completed;
    }

    /**
     * @param positive-int $numberOfWorkers
     */
    private function pool(int $numberOfWorkers, ?ProcessBudget $budget = null): WorkerPool
    {
        $processor = new ChildProcessResultProcessor(
            new Facade,
            $this->createStub(Emitter::class),
            new PassedTests,
            new CodeCoverage,
        );

        $jobRunner = new JobRunner($processor);

        $workers = [];

        for ($id = 0; $id < $numberOfWorkers; $id++) {
            $workers[] = new PersistentWorker($jobRunner, $id);
        }

        if ($budget === null) {
            $budget = new ProcessBudget($numberOfWorkers);
        }

        return new WorkerPool($workers, $budget);
    }

    /**
     * @param list<CompletedWorkUnit> $completed
     *
     * @return list<non-negative-int>
     */
    private function indexesOf(array $completed): array
    {
        $indexes = [];

        foreach ($completed as $unit) {
            $indexes[] = $unit->unit()->index();
        }

        sort($indexes);

        return $indexes;
    }
}
