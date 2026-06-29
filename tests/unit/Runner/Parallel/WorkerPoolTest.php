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
use PHPUnit\Event\Emitter;
use PHPUnit\Event\Facade;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestRunner\ChildProcessResultProcessor;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerSecondTest;
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

        $completed = $this->execute($this->pool(2), $units);

        $this->assertCount(2, $completed);

        foreach ($completed as $unit) {
            $this->assertFalse($unit->crashed());
            $this->assertNotSame('', $unit->serializedResult());
            $this->assertNotNull($unit->nonce());
        }

        $this->assertSame([0, 1], $this->indexesOf($completed));
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

    /**
     * @param list<WorkUnit> $units
     *
     * @return list<CompletedWorkUnit>
     */
    private function execute(WorkerPool $pool, array $units): array
    {
        $completed = [];

        $pool->start();

        try {
            $pool->run(
                $units,
                static function (CompletedWorkUnit $unit) use (&$completed): void
                {
                    $completed[] = $unit;
                },
            );
        } finally {
            $pool->stop();
        }

        return $completed;
    }

    /**
     * @param positive-int $numberOfWorkers
     */
    private function pool(int $numberOfWorkers): WorkerPool
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

        return new WorkerPool($workers);
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
