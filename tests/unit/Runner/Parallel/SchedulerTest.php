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

use function sys_get_temp_dir;
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use PHPUnit\Runner\TestRunHistory\DefaultTestRunHistory;
use PHPUnit\Runner\TestRunHistory\TestRunHistoryId;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerLargeTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerMediumTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerSecondTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerSleepingTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerSmallTest;

#[CoversClass(Scheduler::class)]
#[UsesClass(TestClassWorkUnit::class)]
#[UsesClass(PhptWorkUnit::class)]
#[Small]
final class SchedulerTest extends TestCase
{
    public function testDispatchesTheUnitsWithTheLongestRecordedDurationsFirst(): void
    {
        $cache = $this->cache();

        $cache->setTime(TestRunHistoryId::fromTestClassAndMethodName(WorkerFirstTest::class, 'testStartsTheProcessLocalCounter'), 0.1);
        $cache->setTime(TestRunHistoryId::fromTestClassAndMethodName(WorkerSecondTest::class, 'testThatFails'), 0.5);
        $cache->setTime(TestRunHistoryId::fromTestClassAndMethodName(WorkerSleepingTest::class, 'testThatSleeps'), 5.0);

        $units = [
            new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
            new TestClassWorkUnit(1, WorkerSecondTest::class, [new WorkerSecondTest('testThatFails')]),
            new TestClassWorkUnit(2, WorkerSleepingTest::class, [new WorkerSleepingTest('testThatSleeps')]),
        ];

        $this->assertSame([2, 1, 0], $this->indexesOf(new Scheduler($cache)->schedule($units)));
    }

    public function testDispatchesAUnitWithoutARecordedDurationBeforeEveryOther(): void
    {
        $cache = $this->cache();

        $cache->setTime(TestRunHistoryId::fromTestClassAndMethodName(WorkerFirstTest::class, 'testStartsTheProcessLocalCounter'), 5.0);

        // The duration of the unit at index 1 is unknown: its tests have not
        // run before, and their duration may be arbitrarily large.
        $units = [
            new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
            new TestClassWorkUnit(1, WorkerSecondTest::class, [new WorkerSecondTest('testThatFails')]),
        ];

        $this->assertSame([1, 0], $this->indexesOf(new Scheduler($cache)->schedule($units)));
    }

    public function testUnitsWithEqualDurationEstimatesKeepTheirSuiteOrder(): void
    {
        $units = [
            new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
            new TestClassWorkUnit(1, WorkerSecondTest::class, [new WorkerSecondTest('testThatFails')]),
            new TestClassWorkUnit(2, WorkerSleepingTest::class, [new WorkerSleepingTest('testThatSleeps')]),
        ];

        // Without a result cache, every duration is unknown; the dispatch
        // order is the suite order, as it was before scheduling existed.
        $this->assertSame([0, 1, 2], $this->indexesOf(new Scheduler($this->cache())->schedule($units)));
    }

    public function testEstimatesAUnitWhoseTestsHaveNotRunBeforeFromTheSizeTheyDeclare(): void
    {
        // Nothing is recorded, as in a first run or in a fresh CI container.
        // The sizes the tests declare are what orders the units: the large one
        // first, the small one last.
        $units = [
            new TestClassWorkUnit(0, WorkerSmallTest::class, [new WorkerSmallTest('testOne')]),
            new TestClassWorkUnit(1, WorkerLargeTest::class, [new WorkerLargeTest('testOne')]),
            new TestClassWorkUnit(2, WorkerMediumTest::class, [new WorkerMediumTest('testOne')]),
        ];

        $this->assertSame([1, 2, 0], $this->indexesOf(new Scheduler($this->cache())->schedule($units)));
    }

    public function testDispatchesAUnitThatCannotBeEstimatedAtAllBeforeOneEstimatedFromADeclaredSize(): void
    {
        // The unit at index 1 declares no size and has not run before, so
        // nothing is known about it and its duration may be arbitrarily large.
        $units = [
            new TestClassWorkUnit(0, WorkerLargeTest::class, [new WorkerLargeTest('testOne')]),
            new TestClassWorkUnit(1, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
        ];

        $this->assertSame([1, 0], $this->indexesOf(new Scheduler($this->cache())->schedule($units)));
    }

    public function testPrefersTheRecordedDurationOfATestOverTheSizeItDeclares(): void
    {
        $cache = $this->cache();

        // The small test took 20 seconds when it last ran, which is what its
        // unit is estimated at — the second of the two, which declares the
        // larger size but has not run before, is worth the 10 seconds its size
        // promises.
        $cache->setTime(TestRunHistoryId::fromTestClassAndMethodName(WorkerSmallTest::class, 'testOne'), 20.0);

        $units = [
            new TestClassWorkUnit(0, WorkerSmallTest::class, [new WorkerSmallTest('testOne')]),
            new TestClassWorkUnit(1, WorkerMediumTest::class, [new WorkerMediumTest('testOne')]),
        ];

        $this->assertSame([0, 1], $this->indexesOf(new Scheduler($cache)->schedule($units)));
    }

    public function testSchedulesAPhptTestByItsRecordedDuration(): void
    {
        $file = __DIR__ . '/../../../_files/parallel-worker/worker.phpt';

        $cache = $this->cache();

        $cache->setTime(TestRunHistoryId::fromTestClassAndMethodName(WorkerFirstTest::class, 'testStartsTheProcessLocalCounter'), 0.5);
        $cache->setTime(TestRunHistoryId::fromReorderable(new PhptTestCase($file)), 2.0);

        $units = [
            new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
            new PhptWorkUnit(1, $file),
        ];

        $this->assertSame([1, 0], $this->indexesOf(new Scheduler($cache)->schedule($units)));
    }

    public function testSumsTheRecordedDurationsOfTheTestsThatADataProviderSuiteAggregates(): void
    {
        $cache = $this->cache();

        $cache->setTime(TestRunHistoryId::fromTestClassAndMethodName(WorkerFirstTest::class, 'testStartsTheProcessLocalCounter'), 0.4);
        $cache->setTime(TestRunHistoryId::fromTestClassAndMethodName(WorkerSecondTest::class, 'testThatFails'), 0.5);

        // Two aggregated tests of 0.4 each: the unit's estimate is their sum,
        // 0.8, which outweighs the other unit's 0.5.
        $dataProviderTestSuite = DataProviderTestSuite::empty(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter', $this->createStub(Emitter::class));

        $dataProviderTestSuite->addTest(new WorkerFirstTest('testStartsTheProcessLocalCounter'));
        $dataProviderTestSuite->addTest(new WorkerFirstTest('testStartsTheProcessLocalCounter'));

        $units = [
            new TestClassWorkUnit(0, WorkerSecondTest::class, [new WorkerSecondTest('testThatFails')]),
            new TestClassWorkUnit(1, WorkerFirstTest::class, [$dataProviderTestSuite]),
        ];

        $this->assertSame([1, 0], $this->indexesOf(new Scheduler($cache)->schedule($units)));
    }

    /**
     * A result cache that is never loaded from or persisted to its file: the
     * tests only use the times set on the instance.
     */
    private function cache(): DefaultTestRunHistory
    {
        return new DefaultTestRunHistory(sys_get_temp_dir() . '/phpunit-scheduler-test.result.cache');
    }

    /**
     * @param list<WorkUnit> $units
     *
     * @return list<non-negative-int>
     */
    private function indexesOf(array $units): array
    {
        $indexes = [];

        foreach ($units as $unit) {
            $indexes[] = $unit->index();
        }

        return $indexes;
    }
}
