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
use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\RetryTestSuite;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\TestFixture\ParallelWorker\WorkerDataProvidedTest;
use ReflectionProperty;

#[CoversClass(RetrySuiteDescriptor::class)]
#[UsesClass(TestCaseDescriptor::class)]
#[UsesClass(WorkerDataProvider::class)]
#[Small]
final class RetrySuiteDescriptorTest extends TestCase
{
    public function testRebuildsTheSuiteUnderTheNameItWasDescribedWith(): void
    {
        $suite = $this->rebuild($this->suite());

        $this->assertInstanceOf(RetryTestSuite::class, $suite);
        $this->assertSame(WorkerDataProvidedTest::class . '::testWithNumberedDataSets', $suite->name());
    }

    public function testRebuildsTheNumberOfAttemptsTheTestMethodIsAllowed(): void
    {
        $this->assertSame(3, $this->rebuild($this->suite())->maxAttempts());
    }

    public function testRebuildsTheFirstAttempt(): void
    {
        $tests = $this->rebuild($this->suite())->tests();

        $this->assertCount(1, $tests);
        $this->assertInstanceOf(WorkerDataProvidedTest::class, $tests[0]);
        $this->assertSame('testWithNumberedDataSets', $tests[0]->name());
        $this->assertSame([1], $tests[0]->providedData());
    }

    public function testRebuildsASuiteThatBuildsEachFurtherAttemptFromTheSameDescription(): void
    {
        $suite = $this->rebuild($this->suite());

        // Only the first attempt is described; the additional attempts are
        // built inside the worker, from that same description, so that each
        // one runs on a test case of its own.
        $furtherAttempt = $this->additionalAttemptFactoryOf($suite)();

        $this->assertInstanceOf(WorkerDataProvidedTest::class, $furtherAttempt);
        $this->assertSame('testWithNumberedDataSets', $furtherAttempt->name());
        $this->assertSame([1], $furtherAttempt->providedData());
        $this->assertNotSame($suite->tests()[0], $furtherAttempt);
    }

    public function testDescribesTheFirstAttemptOfATestThatTestSelectionSelected(): void
    {
        // Only the first attempt is in the suite, so test selection takes
        // either it or nothing. A suite the selection emptied is not described
        // at all: it is skipped where the members of a unit are collected.
        $suite = $this->suite();

        $factory = new Factory;

        $factory->addIncludeNameFilter('testWithNumberedDataSets#0');

        $suite->injectFilter($factory);

        $this->assertCount(1, $this->rebuild($suite)->tests());
    }

    private function suite(): RetryTestSuite
    {
        $test = new WorkerDataProvidedTest('testWithNumberedDataSets');

        $test->setData(0, [1]);

        return $this->suiteFor($test);
    }

    /**
     * A suite of three attempts, as the parent process has it before the unit
     * that contains it is described.
     */
    private function suiteFor(WorkerDataProvidedTest $test): RetryTestSuite
    {
        return RetryTestSuite::fromTestCase(
            WorkerDataProvidedTest::class . '::testWithNumberedDataSets',
            $this->createStub(Emitter::class),
            $test,
            3,
            static function (): TestCase
            {
                return new WorkerDataProvidedTest('testWithNumberedDataSets');
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
        return RetrySuiteDescriptor::fromTestSuite($suite, WorkerDataProvidedTest::class)->test(
            WorkerDataProvidedTest::class,
            new WorkerDataProvider($this->createStub(Emitter::class)),
        );
    }
}
