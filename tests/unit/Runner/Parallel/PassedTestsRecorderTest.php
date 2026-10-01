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

use function hrtime;
use Exception;
use PHPUnit\Event\Code\Phpt;
use PHPUnit\Event\Code\TestMethodBuilder;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\CollectingDispatcher;
use PHPUnit\Event\DirectDispatcher;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\Telemetry;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\DeprecationTriggeredSubscriber;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErroredSubscriber;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber;
use PHPUnit\Event\TestSuite\Finished;
use PHPUnit\Event\TestSuite\FinishedSubscriber;
use PHPUnit\Event\TestSuite\TestSuite as TestSuiteValue;
use PHPUnit\Event\TestSuite\TestSuiteBuilder;
use PHPUnit\Event\TypeMap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\RepeatTestSuite;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\TestFixture\ParallelWorker\WorkerDependingTest;
use PHPUnit\TestRunner\TestResult\PassedTests;

/**
 * The tests run in separate processes: the recorder records into the
 * PassedTests singleton, which these tests reset, and which the process that
 * runs these tests uses for the tests it runs itself.
 */
#[CoversClass(PassedTestsRecorder::class)]
#[CoversClass(Subscriber::class)]
#[CoversClass(TestErroredSubscriber::class)]
#[CoversClass(TestFailedSubscriber::class)]
#[CoversClass(TestSuiteFinishedSubscriber::class)]
#[RunTestsInSeparateProcesses]
#[Small]
final class PassedTestsRecorderTest extends TestCase
{
    protected function setUp(): void
    {
        PassedTests::instance()->reset();
    }

    public function testRecordsATestMethodWithADataProviderAsPassedWhenNoneOfItsDataSetsFailedOrErrored(): void
    {
        (new PassedTestsRecorder)->testSuiteFinished($this->dataProviderSuite());

        $this->assertTrue(PassedTests::instance()->hasTestMethodPassed(WorkerDependingTest::class . '::testDataProvidedConsumer'));
    }

    public function testDoesNotRecordATestMethodWithADataProviderAsPassedWhenOneOfItsDataSetsFailedOrErrored(): void
    {
        $recorder = new PassedTestsRecorder;

        $recorder->testFailedOrErrored(TestMethodBuilder::fromTestCase($this->dataSet()));
        $recorder->testSuiteFinished($this->dataProviderSuite());

        $this->assertFalse(PassedTests::instance()->hasTestMethodPassed(WorkerDependingTest::class . '::testDataProvidedConsumer'));
    }

    public function testRecordsARepeatedTestMethodAsPassedWhenNoneOfItsRepetitionsFailedOrErrored(): void
    {
        (new PassedTestsRecorder)->testSuiteFinished($this->repeatSuite());

        $this->assertTrue(PassedTests::instance()->hasTestMethodPassed(WorkerDependingTest::class . '::testConsumer'));
    }

    public function testLeavesTheDecisionWhetherARepeatedDataSetPassedToTheSuiteOfItsDataProvider(): void
    {
        $suite = RepeatTestSuite::fromTests(
            WorkerDependingTest::class . '::testDataProvidedConsumer#0',
            $this->createStub(Emitter::class),
            [$this->dataSet()],
            1,
        );

        (new PassedTestsRecorder)->testSuiteFinished(TestSuiteBuilder::from($suite));

        $this->assertFalse(PassedTests::instance()->hasTestMethodPassed(WorkerDependingTest::class . '::testDataProvidedConsumer'));
    }

    public function testDoesNotRecordATestClassAsPassed(): void
    {
        $suite = TestSuite::empty(WorkerDependingTest::class, $this->createStub(Emitter::class));

        $suite->addTest(new WorkerDependingTest('testProducer'));

        (new PassedTestsRecorder)->testSuiteFinished(TestSuiteBuilder::from($suite));

        $this->assertFalse(PassedTests::instance()->hasTestClassPassed(WorkerDependingTest::class));
    }

    public function testIgnoresAFailureOfATestThatIsNotATestMethod(): void
    {
        $recorder = new PassedTestsRecorder;

        $recorder->testFailedOrErrored(new Phpt(__FILE__));
        $recorder->testSuiteFinished($this->dataProviderSuite());

        $this->assertTrue(PassedTests::instance()->hasTestMethodPassed(WorkerDependingTest::class . '::testDataProvidedConsumer'));
    }

    public function testIsToldAboutFailedTestsAndFinishedSuitesByTheSubscribersItRegisters(): void
    {
        $dispatcher = $this->dispatcher();

        (new PassedTestsRecorder)->registerWith($dispatcher);

        $dispatcher->dispatch(
            new Failed(
                $this->telemetryInfo(),
                TestMethodBuilder::fromTestCase($this->dataSet()),
                ThrowableBuilder::from(new Exception),
                null,
            ),
        );

        $dispatcher->dispatch(new Finished($this->telemetryInfo(), $this->dataProviderSuite()));
        $dispatcher->dispatch(new Finished($this->telemetryInfo(), $this->repeatSuite()));

        $this->assertFalse(PassedTests::instance()->hasTestMethodPassed(WorkerDependingTest::class . '::testDataProvidedConsumer'));
        $this->assertTrue(PassedTests::instance()->hasTestMethodPassed(WorkerDependingTest::class . '::testConsumer'));
    }

    public function testIsToldAboutErroredTestsByTheSubscribersItRegisters(): void
    {
        $dispatcher = $this->dispatcher();

        (new PassedTestsRecorder)->registerWith($dispatcher);

        $dispatcher->dispatch(
            new Errored(
                $this->telemetryInfo(),
                TestMethodBuilder::fromTestCase(new WorkerDependingTest('testConsumer')),
                ThrowableBuilder::from(new Exception),
            ),
        );

        $dispatcher->dispatch(new Finished($this->telemetryInfo(), $this->repeatSuite()));

        $this->assertFalse(PassedTests::instance()->hasTestMethodPassed(WorkerDependingTest::class . '::testConsumer'));
    }

    private function dataSet(): WorkerDependingTest
    {
        $test = new WorkerDependingTest('testDataProvidedConsumer');

        $test->setData(0, [true]);

        return $test;
    }

    private function dataProviderSuite(): TestSuiteValue
    {
        $suite = DataProviderTestSuite::empty(WorkerDependingTest::class . '::testDataProvidedConsumer', $this->createStub(Emitter::class));

        $suite->addTest($this->dataSet());

        return TestSuiteBuilder::from($suite);
    }

    private function repeatSuite(): TestSuiteValue
    {
        $first  = new WorkerDependingTest('testConsumer');
        $second = new WorkerDependingTest('testConsumer');

        $first->setRepetition(1, 2);
        $second->setRepetition(2, 2);

        return TestSuiteBuilder::from(
            RepeatTestSuite::fromTests(
                WorkerDependingTest::class . '::testConsumer',
                $this->createStub(Emitter::class),
                [$first, $second],
                1,
            ),
        );
    }

    private function dispatcher(): CollectingDispatcher
    {
        $typeMap = new TypeMap;

        $typeMap->addMapping(DeprecationTriggeredSubscriber::class, DeprecationTriggered::class);
        $typeMap->addMapping(ErroredSubscriber::class, Errored::class);
        $typeMap->addMapping(FailedSubscriber::class, Failed::class);
        $typeMap->addMapping(FinishedSubscriber::class, Finished::class);

        return new CollectingDispatcher(new DirectDispatcher($typeMap));
    }

    private function telemetryInfo(): Telemetry\Info
    {
        return new Telemetry\Info(
            new Telemetry\Snapshot(
                HRTime::fromSecondsAndNanoseconds(...hrtime(false)),
                Telemetry\MemoryUsage::fromBytes(1000),
                Telemetry\MemoryUsage::fromBytes(2000),
                new Telemetry\GarbageCollectorStatus(0, 0, 0, 0, 0.0, 0.0, 0.0, 0.0, false, false, false, 0),
                Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
                Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
                Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            ),
            Telemetry\Duration::fromSecondsAndNanoseconds(123, 456),
            Telemetry\MemoryUsage::fromBytes(2000),
            Telemetry\Duration::fromSecondsAndNanoseconds(234, 567),
            Telemetry\MemoryUsage::fromBytes(3000),
            Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            12345,
        );
    }
}
