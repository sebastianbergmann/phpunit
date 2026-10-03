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

use PHPUnit\Event\Emitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\Reorderable;
use PHPUnit\Framework\RepeatTestSuite;
use PHPUnit\Framework\RetryTestSuite;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\ParallelWorker\WorkerDependingTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;

#[CoversClass(TestDescriptor::class)]
#[UsesClass(DataProviderSuiteDescriptor::class)]
#[UsesClass(RepeatSuiteDescriptor::class)]
#[UsesClass(RetrySuiteDescriptor::class)]
#[UsesClass(TestCaseDescriptor::class)]
#[UsesClass(WorkerDataProvider::class)]
#[Small]
final class TestDescriptorTest extends TestCase
{
    public function testDescribesATestCaseAsATestCase(): void
    {
        $descriptor = TestDescriptor::from($this->testCase(), WorkerFirstTest::class);

        $this->assertInstanceOf(TestCaseDescriptor::class, $descriptor);
    }

    public function testDescribesTheSuiteOfADataProviderMethodAsASuite(): void
    {
        $suite = DataProviderTestSuite::empty(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter', $this->createStub(Emitter::class));

        $suite->addTest($this->testCase());

        $descriptor = TestDescriptor::from($suite, WorkerFirstTest::class);

        $this->assertInstanceOf(DataProviderSuiteDescriptor::class, $descriptor);
    }

    public function testDescribesTheSuiteOfARetriedTestMethodAsASuite(): void
    {
        $test = $this->testCase();

        $suite = RetryTestSuite::fromTestCase(
            WorkerFirstTest::class . '::testStartsTheProcessLocalCounter',
            $this->createStub(Emitter::class),
            $test,
            2,
            static function () use ($test): TestCase
            {
                return $test;
            },
        );

        $descriptor = TestDescriptor::from($suite, WorkerFirstTest::class);

        $this->assertInstanceOf(RetrySuiteDescriptor::class, $descriptor);
    }

    public function testDescribesTheSuiteOfARepeatedTestMethodAsASuite(): void
    {
        $suite = RepeatTestSuite::fromTests(
            WorkerFirstTest::class . '::testStartsTheProcessLocalCounter',
            $this->createStub(Emitter::class),
            [$this->testCase()],
            1,
        );

        $descriptor = TestDescriptor::from($suite, WorkerFirstTest::class);

        $this->assertInstanceOf(RepeatSuiteDescriptor::class, $descriptor);
    }

    public function testDescribesTheMembersOfASuiteRecursively(): void
    {
        // The suite of a data provider method whose tests are retried nests
        // one suite inside the other; both must travel to the worker as
        // suites, so the members of a suite are described as such however
        // deeply they are nested.
        $test = $this->testCase();

        $inner = RetryTestSuite::fromTestCase(
            WorkerFirstTest::class . '::testStartsTheProcessLocalCounter',
            $this->createStub(Emitter::class),
            $test,
            2,
            static function () use ($test): TestCase
            {
                return $test;
            },
        );

        $outer = DataProviderTestSuite::empty(WorkerFirstTest::class . '::testStartsTheProcessLocalCounter', $this->createStub(Emitter::class));

        $outer->addTest($inner);

        $rebuilt = TestDescriptor::from($outer, WorkerFirstTest::class)->test(
            WorkerFirstTest::class,
            new WorkerDataProvider($this->createStub(Emitter::class)),
        );

        $this->assertInstanceOf(DataProviderTestSuite::class, $rebuilt);
        $this->assertInstanceOf(RetryTestSuite::class, $rebuilt->tests()[0]);
    }

    public function testRebuildsATestCaseWithTheDependenciesOfItsTestMethod(): void
    {
        $member = TestDescriptor::from(new WorkerDependingTest('testConsumer'), WorkerDependingTest::class)->member(
            WorkerDependingTest::class,
            new WorkerDataProvider($this->createStub(Emitter::class)),
        );

        $this->assertSame([WorkerDependingTest::class . '::testProducer'], $this->dependencyTargetsOf($member));
    }

    public function testRebuildsTheSuiteOfADataProviderMethodWithTheDependenciesOfItsTestMethod(): void
    {
        $test = new WorkerDependingTest('testDataProvidedConsumer');

        $test->setData(0, [true]);

        $suite = DataProviderTestSuite::empty(WorkerDependingTest::class . '::testDataProvidedConsumer', $this->createStub(Emitter::class));

        $suite->addTest($test);

        $member = TestDescriptor::from($suite, WorkerDependingTest::class)->member(
            WorkerDependingTest::class,
            new WorkerDataProvider($this->createStub(Emitter::class)),
        );

        $this->assertInstanceOf(DataProviderTestSuite::class, $member);
        $this->assertSame([WorkerDependingTest::class . '::testProducer'], $this->dependencyTargetsOf($member));
        $this->assertSame([WorkerDependingTest::class . '::testProducer'], $this->dependencyTargetsOf($member->tests()[0]));
    }

    public function testRebuildsTheSuiteOfARetriedTestMethodWithTheDependenciesOfItsTestMethod(): void
    {
        $test = new WorkerDependingTest('testConsumer');

        $suite = RetryTestSuite::fromTestCase(
            WorkerDependingTest::class . '::testConsumer',
            $this->createStub(Emitter::class),
            $test,
            2,
            static function () use ($test): TestCase
            {
                return $test;
            },
        );

        $member = TestDescriptor::from($suite, WorkerDependingTest::class)->member(
            WorkerDependingTest::class,
            new WorkerDataProvider($this->createStub(Emitter::class)),
        );

        $this->assertInstanceOf(RetryTestSuite::class, $member);
        $this->assertSame([WorkerDependingTest::class . '::testProducer'], $this->dependencyTargetsOf($member));
    }

    public function testRebuildsTheSuiteOfARepeatedTestMethodWithTheDependenciesOfItsTestMethod(): void
    {
        $suite = RepeatTestSuite::fromTests(
            WorkerDependingTest::class . '::testConsumer',
            $this->createStub(Emitter::class),
            [new WorkerDependingTest('testConsumer')],
            1,
        );

        $member = TestDescriptor::from($suite, WorkerDependingTest::class)->member(
            WorkerDependingTest::class,
            new WorkerDataProvider($this->createStub(Emitter::class)),
        );

        $this->assertInstanceOf(RepeatTestSuite::class, $member);
        $this->assertSame([WorkerDependingTest::class . '::testProducer'], $this->dependencyTargetsOf($member));
    }

    private function testCase(): WorkerFirstTest
    {
        return new WorkerFirstTest('testStartsTheProcessLocalCounter');
    }

    /**
     * @return list<string>
     */
    private function dependencyTargetsOf(Test $test): array
    {
        $this->assertInstanceOf(Reorderable::class, $test);

        $targets = [];

        foreach ($test->requires() as $dependency) {
            $targets[] = $dependency->getTarget();
        }

        return $targets;
    }
}
