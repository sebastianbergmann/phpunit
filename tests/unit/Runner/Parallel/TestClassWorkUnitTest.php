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
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\Runner\TestRunHistory\DefaultTestRunHistory;
use PHPUnit\Runner\TestRunHistory\TestRunHistoryId;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerSecondTest;

#[CoversClass(TestClassWorkUnit::class)]
#[Small]
final class TestClassWorkUnitTest extends TestCase
{
    public function testHasIndex(): void
    {
        $this->assertSame(3, $this->unit()->index());
    }

    public function testHasClassName(): void
    {
        $this->assertSame(WorkerFirstTest::class, $this->unit()->className());
    }

    public function testHasTests(): void
    {
        $tests = $this->unit()->tests();

        $this->assertCount(1, $tests);
        $this->assertInstanceOf(WorkerFirstTest::class, $tests[0]);
    }

    public function testIsNamedAfterItsClass(): void
    {
        $this->assertSame(WorkerFirstTest::class, $this->unit()->name());
    }

    public function testHasTheSummedDurationsRecordedForItsTests(): void
    {
        $testRunHistory = $this->testRunHistory();

        $testRunHistory->setTime(TestRunHistoryId::fromTestClassAndMethodName(WorkerSecondTest::class, 'testThatFails'), 0.25);
        $testRunHistory->setTime(TestRunHistoryId::fromTestClassAndMethodName(WorkerSecondTest::class, 'testThatKillsTheWorkerProcess'), 0.75);

        $unit = new TestClassWorkUnit(
            0,
            WorkerSecondTest::class,
            [
                new WorkerSecondTest('testThatFails'),
                new WorkerSecondTest('testThatKillsTheWorkerProcess'),
            ],
        );

        $this->assertSame(1.0, $unit->duration($testRunHistory));
    }

    public function testSumsTheDurationsRecordedForTheTestsThatAnAggregatingSuiteContains(): void
    {
        $testRunHistory = $this->testRunHistory();

        $testRunHistory->setTime(TestRunHistoryId::fromTestClassAndMethodName(WorkerFirstTest::class, 'testStartsTheProcessLocalCounter'), 0.5);

        // The tests of a data provider method travel as one member of the
        // unit; what the unit is estimated to cost is what all of them cost
        // together.
        $suite = DataProviderTestSuite::empty(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter');

        $suite->addTest(new WorkerFirstTest('testStartsTheProcessLocalCounter'));
        $suite->addTest(new WorkerFirstTest('testStartsTheProcessLocalCounter'));

        $unit = new TestClassWorkUnit(0, WorkerFirstTest::class, [$suite]);

        $this->assertSame(1.0, $unit->duration($testRunHistory));
    }

    public function testSumsTheDurationsRecordedOnlyForTheTestsThatTestSelectionSelected(): void
    {
        // What the unit is estimated to cost is what the tests it will
        // actually run cost: the test that test selection excluded is not
        // dispatched and must not be counted.
        $suite = DataProviderTestSuite::empty(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter');

        $first = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $first->setData('first data set', [1]);

        $second = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $second->setData('second data set', [2]);

        $suite->addTest($first);
        $suite->addTest($second);

        $testRunHistory = $this->testRunHistory();

        $testRunHistory->setTime(TestRunHistoryId::fromReorderable($first), 0.25);
        $testRunHistory->setTime(TestRunHistoryId::fromReorderable($second), 0.5);

        $factory = new Factory;

        $factory->addIncludeNameFilter('testStartsTheProcessLocalCounter#second data set');

        $suite->injectFilter($factory);

        $unit = new TestClassWorkUnit(0, WorkerFirstTest::class, [$suite]);

        $this->assertSame(0.5, $unit->duration($testRunHistory));
    }

    public function testHasNoDurationWhenItsTestsHaveNotRunBefore(): void
    {
        $this->assertSame(0.0, $this->unit()->duration($this->testRunHistory()));
    }

    private function unit(): TestClassWorkUnit
    {
        return new TestClassWorkUnit(
            3,
            WorkerFirstTest::class,
            [new WorkerFirstTest('testStartsTheProcessLocalCounter')],
        );
    }

    /**
     * A test run history that is never loaded from or persisted to its file:
     * the tests only use the times set on the instance.
     */
    private function testRunHistory(): DefaultTestRunHistory
    {
        return new DefaultTestRunHistory(sys_get_temp_dir() . '/phpunit-test-class-work-unit-test.result.cache');
    }
}
