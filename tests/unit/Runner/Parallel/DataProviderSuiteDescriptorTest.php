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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\RepeatTestSuite;
use PHPUnit\Framework\RetryTestSuite;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;

#[CoversClass(DataProviderSuiteDescriptor::class)]
#[UsesClass(TestDescriptor::class)]
#[UsesClass(TestCaseDescriptor::class)]
#[UsesClass(RepeatSuiteDescriptor::class)]
#[UsesClass(RetrySuiteDescriptor::class)]
#[Small]
final class DataProviderSuiteDescriptorTest extends TestCase
{
    public function testRebuildsTheSuiteUnderTheNameItWasDescribedWith(): void
    {
        $suite = $this->rebuild($this->suite());

        $this->assertInstanceOf(DataProviderTestSuite::class, $suite);
        $this->assertSame(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter', $suite->name());
    }

    public function testRebuildsTheTestsOfTheDataProviderInTheOrderTheyWereDescribedIn(): void
    {
        $tests = $this->rebuild($this->suite())->tests();

        $this->assertCount(2, $tests);

        $this->assertInstanceOf(WorkerFirstTest::class, $tests[0]);
        $this->assertSame('first data set', $tests[0]->dataName());
        $this->assertSame([1], $tests[0]->providedData());

        $this->assertInstanceOf(WorkerFirstTest::class, $tests[1]);
        $this->assertSame('second data set', $tests[1]->dataName());
        $this->assertSame([2], $tests[1]->providedData());
    }

    public function testRebuildsASuiteThatAggregatesNoTestsAtAll(): void
    {
        // A data provider that provided nothing leaves an empty suite behind;
        // it travels to the worker so that the worker reports it just as a
        // sequential run would.
        $suite = $this->rebuild(DataProviderTestSuite::empty(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter'));

        $this->assertSame([], $suite->tests());
    }

    public function testDescribesOnlyTheTestsThatTestSelectionSelected(): void
    {
        $suite = $this->suite();

        $this->select($suite, 'testStartsTheProcessLocalCounter#second data set');

        $tests = $this->rebuild($suite)->tests();

        $this->assertCount(1, $tests);
        $this->assertInstanceOf(WorkerFirstTest::class, $tests[0]);
        $this->assertSame('second data set', $tests[0]->dataName());
    }

    public function testDoesNotDescribeTheAttemptsOfARetriedTestThatTestSelectionExcluded(): void
    {
        // The filter accepts every suite and applies the selection to the
        // tests inside it, so the attempts of the data set that the selection
        // excluded are still yielded as a suite, an empty one. That suite runs
        // nothing in a sequential run and must therefore not travel to the
        // worker.
        $suite = DataProviderTestSuite::empty(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter');

        $suite->addTest($this->attemptsFor('first data set'));
        $suite->addTest($this->attemptsFor('second data set'));

        $this->select($suite, 'testStartsTheProcessLocalCounter#second data set');

        $members = $this->rebuild($suite)->tests();

        $this->assertCount(1, $members);
        $this->assertInstanceOf(RetryTestSuite::class, $members[0]);

        $attempts = $members[0]->tests();

        $this->assertCount(1, $attempts);
        $this->assertInstanceOf(WorkerFirstTest::class, $attempts[0]);
        $this->assertSame('second data set', $attempts[0]->dataName());
    }

    public function testDoesNotDescribeTheRepetitionsOfARepeatedTestThatTestSelectionExcluded(): void
    {
        // The filter accepts every suite and applies the selection to the
        // tests inside it, so the repetitions of the data set that the
        // selection excluded are still yielded as a suite, an empty one. That
        // suite runs nothing in a sequential run and must therefore not travel
        // to the worker.
        $suite = DataProviderTestSuite::empty(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter');

        $suite->addTest($this->repetitionsFor('first data set'));
        $suite->addTest($this->repetitionsFor('second data set'));

        $this->select($suite, 'testStartsTheProcessLocalCounter#second data set');

        $members = $this->rebuild($suite)->tests();

        $this->assertCount(1, $members);
        $this->assertInstanceOf(RepeatTestSuite::class, $members[0]);

        $repetitions = $members[0]->tests();

        $this->assertCount(2, $repetitions);

        foreach ($repetitions as $repetition) {
            $this->assertInstanceOf(WorkerFirstTest::class, $repetition);
            $this->assertSame('second data set', $repetition->dataName());
        }
    }

    public function testCannotDescribeASuiteWhoseTestsCarryDataThatCannotBeSerialized(): void
    {
        // A closure cannot be serialized, so a suite whose tests carry one
        // cannot be described for transport to a worker.
        $closure = static function (): void
        {
        };

        $test = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $test->setData('the data set', [$closure]);

        $suite = DataProviderTestSuite::empty(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter');

        $suite->addTest($test);

        $this->expectException(WorkerException::class);

        DataProviderSuiteDescriptor::fromTestSuite($suite, WorkerFirstTest::class);
    }

    private function suite(): DataProviderTestSuite
    {
        $suite = DataProviderTestSuite::empty(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter');

        $first = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $first->setData('first data set', [1]);

        $second = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $second->setData('second data set', [2]);

        $suite->addTest($first);
        $suite->addTest($second);

        return $suite;
    }

    /**
     * The attempts of one data set of a retried test method, as the parent
     * process has them before the unit that contains them is described.
     *
     * @param non-empty-string $dataName
     */
    private function attemptsFor(string $dataName): RetryTestSuite
    {
        $test = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $test->setData($dataName, [1]);

        return RetryTestSuite::fromTestCase(
            WorkerFirstTest::class . '::testStartsTheProcessLocalCounter',
            $test,
            2,
            static function () use ($dataName): TestCase
            {
                $attempt = new WorkerFirstTest('testStartsTheProcessLocalCounter');

                $attempt->setData($dataName, [1]);

                return $attempt;
            },
        );
    }

    /**
     * The repetitions of one data set of a repeated test method, as the parent
     * process has them before the unit that contains them is described.
     *
     * @param non-empty-string $dataName
     */
    private function repetitionsFor(string $dataName): RepeatTestSuite
    {
        $repetitions = [];

        for ($repetition = 1; $repetition <= 2; $repetition++) {
            $test = new WorkerFirstTest('testStartsTheProcessLocalCounter');

            $test->setData($dataName, [$repetition]);
            $test->setRepetition($repetition, 2);

            $repetitions[] = $test;
        }

        return RepeatTestSuite::fromTests(
            WorkerFirstTest::class . '::testStartsTheProcessLocalCounter',
            $repetitions,
            1,
        );
    }

    /**
     * Apply test selection to the suite, the way --filter does.
     *
     * @param non-empty-string $name
     */
    private function select(DataProviderTestSuite $suite, string $name): void
    {
        $factory = new Factory;

        $factory->addIncludeNameFilter($name);

        $suite->injectFilter($factory);
    }

    /**
     * Describe the suite as the parent process does and rebuild it as the
     * worker process does.
     */
    private function rebuild(DataProviderTestSuite $suite): DataProviderTestSuite
    {
        return DataProviderSuiteDescriptor::fromTestSuite($suite, WorkerFirstTest::class)->test(WorkerFirstTest::class);
    }
}
