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
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;

#[CoversClass(DataProviderSuiteDescriptor::class)]
#[UsesClass(TestDescriptor::class)]
#[UsesClass(TestCaseDescriptor::class)]
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
     * Describe the suite as the parent process does and rebuild it as the
     * worker process does.
     */
    private function rebuild(DataProviderTestSuite $suite): DataProviderTestSuite
    {
        return DataProviderSuiteDescriptor::fromTestSuite($suite, WorkerFirstTest::class)->test(WorkerFirstTest::class);
    }
}
