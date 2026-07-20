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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Phpt\TestCase as PhptTestCase;
use PHPUnit\Runner\ResultCache\DefaultResultCache;
use PHPUnit\Runner\ResultCache\ResultCacheId;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerSecondTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerSleepingTest;

#[CoversClass(Scheduler::class)]
#[UsesClass(TestClassWorkUnit::class)]
#[UsesClass(PhptWorkUnit::class)]
#[Small]
final class SchedulerTest extends TestCase
{
    public function testDispatchesTheUnitsWithTheLongestRecordedDurationsFirst(): void
    {
        $cache = $this->cache();

        $cache->setTime(ResultCacheId::fromTestClassAndMethodName(WorkerFirstTest::class, 'testStartsTheProcessLocalCounter'), 0.1);
        $cache->setTime(ResultCacheId::fromTestClassAndMethodName(WorkerSecondTest::class, 'testThatFails'), 0.5);
        $cache->setTime(ResultCacheId::fromTestClassAndMethodName(WorkerSleepingTest::class, 'testThatSleeps'), 5.0);

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

        $cache->setTime(ResultCacheId::fromTestClassAndMethodName(WorkerFirstTest::class, 'testStartsTheProcessLocalCounter'), 5.0);

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

    public function testSchedulesAPhptTestByItsRecordedDuration(): void
    {
        $file = __DIR__ . '/../../../_files/parallel-worker/worker.phpt';

        $cache = $this->cache();

        $cache->setTime(ResultCacheId::fromTestClassAndMethodName(WorkerFirstTest::class, 'testStartsTheProcessLocalCounter'), 0.5);
        $cache->setTime(ResultCacheId::fromReorderable(new PhptTestCase($file)), 2.0);

        $units = [
            new TestClassWorkUnit(0, WorkerFirstTest::class, [new WorkerFirstTest('testStartsTheProcessLocalCounter')]),
            new PhptWorkUnit(1, $file),
        ];

        $this->assertSame([1, 0], $this->indexesOf(new Scheduler($cache)->schedule($units)));
    }

    public function testSumsTheRecordedDurationsOfTheTestsThatADataProviderSuiteAggregates(): void
    {
        $cache = $this->cache();

        $cache->setTime(ResultCacheId::fromTestClassAndMethodName(WorkerFirstTest::class, 'testStartsTheProcessLocalCounter'), 0.4);
        $cache->setTime(ResultCacheId::fromTestClassAndMethodName(WorkerSecondTest::class, 'testThatFails'), 0.5);

        // Two aggregated tests of 0.4 each: the unit's estimate is their sum,
        // 0.8, which outweighs the other unit's 0.5.
        $dataProviderTestSuite = DataProviderTestSuite::empty(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter');

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
    private function cache(): DefaultResultCache
    {
        return new DefaultResultCache(sys_get_temp_dir() . '/phpunit-scheduler-test.result.cache');
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
