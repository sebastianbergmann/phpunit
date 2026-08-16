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

use function assert;
use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\RetryTestSuite;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;
use ReflectionProperty;

#[CoversClass(RetrySuiteDescriptor::class)]
#[UsesClass(TestCaseDescriptor::class)]
#[Small]
final class RetrySuiteDescriptorTest extends TestCase
{
    public function testRebuildsTheSuiteUnderTheNameItWasDescribedWith(): void
    {
        $suite = $this->rebuild($this->suite());

        $this->assertInstanceOf(RetryTestSuite::class, $suite);
        $this->assertSame(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter', $suite->name());
    }

    public function testRebuildsTheNumberOfAttemptsTheTestMethodIsAllowed(): void
    {
        $this->assertSame(3, $this->rebuild($this->suite())->maxAttempts());
    }

    public function testRebuildsTheFirstAttempt(): void
    {
        $tests = $this->rebuild($this->suite())->tests();

        $this->assertCount(1, $tests);
        $this->assertInstanceOf(WorkerFirstTest::class, $tests[0]);
        $this->assertSame('testStartsTheProcessLocalCounter', $tests[0]->name());
        $this->assertSame(['the data set'], $tests[0]->providedData());
    }

    public function testRebuildsASuiteThatBuildsEachFurtherAttemptFromTheSameDescription(): void
    {
        $suite = $this->rebuild($this->suite());

        // Only the first attempt is described; the additional attempts are
        // built inside the worker, from that same description, so that each
        // one runs on a test case of its own.
        $furtherAttempt = $this->additionalAttemptFactoryOf($suite)();

        $this->assertInstanceOf(WorkerFirstTest::class, $furtherAttempt);
        $this->assertSame('testStartsTheProcessLocalCounter', $furtherAttempt->name());
        $this->assertSame(['the data set'], $furtherAttempt->providedData());
        $this->assertNotSame($suite->tests()[0], $furtherAttempt);
    }

    public function testDescribesTheFirstAttemptOfATestThatTestSelectionSelected(): void
    {
        // Only the first attempt is in the suite, so test selection takes
        // either it or nothing. A suite the selection emptied is not described
        // at all: it is skipped where the members of a unit are collected.
        $suite = $this->suite();

        $factory = new Factory;

        $factory->addIncludeNameFilter('testStartsTheProcessLocalCounter#0');

        $suite->injectFilter($factory);

        $this->assertCount(1, $this->rebuild($suite)->tests());
    }

    public function testCannotDescribeASuiteWhoseTestCarriesDataThatCannotBeSerialized(): void
    {
        // A closure cannot be serialized, so a suite whose test carries one
        // cannot be described for transport to a worker.
        $closure = static function (): void
        {
        };

        $test = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $test->setData(0, [$closure]);

        $this->expectException(WorkerException::class);

        RetrySuiteDescriptor::fromTestSuite($this->suiteFor($test), WorkerFirstTest::class);
    }

    private function suite(): RetryTestSuite
    {
        $test = new WorkerFirstTest('testStartsTheProcessLocalCounter');

        $test->setData(0, ['the data set']);

        return $this->suiteFor($test);
    }

    /**
     * A suite of three attempts, as the parent process has it before the unit
     * that contains it is described.
     */
    private function suiteFor(WorkerFirstTest $test): RetryTestSuite
    {
        return RetryTestSuite::fromTestCase(
            WorkerFirstTest::class . '::testStartsTheProcessLocalCounter',
            $test,
            3,
            static function (): TestCase
            {
                return new WorkerFirstTest('testStartsTheProcessLocalCounter');
            },
        );
    }

    /**
     * @return Closure(): TestCase
     */
    private function additionalAttemptFactoryOf(RetryTestSuite $suite): Closure
    {
        $factory = new ReflectionProperty(RetryTestSuite::class, 'additionalAttemptFactory')->getValue($suite);

        assert($factory instanceof Closure);

        return $factory;
    }

    /**
     * Describe the suite as the parent process does and rebuild it as the
     * worker process does.
     */
    private function rebuild(RetryTestSuite $suite): RetryTestSuite
    {
        return RetrySuiteDescriptor::fromTestSuite($suite, WorkerFirstTest::class)->test(WorkerFirstTest::class);
    }
}
