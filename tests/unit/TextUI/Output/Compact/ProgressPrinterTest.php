<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output\Compact\ProgressPrinter;

use const PHP_EOL;
use function file_get_contents;
use function hrtime;
use AssertionError;
use Closure;
use Exception;
use LogicException;
use PHPUnit\Event\Code\ClassMethod;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Event;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Event\Telemetry\CpuTime;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\AfterLastTestMethodErrored;
use PHPUnit\Event\Test\AfterLastTestMethodFailed;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\BeforeFirstTestMethodFailed;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PrintedUnexpectedOutput;
use PHPUnit\Event\TestData\DataFromDataProvider;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TextUI\Output\Printer;
use RuntimeException;

#[CoversClass(ProgressPrinter::class)]
#[CoversClass(Subscriber::class)]
#[CoversClass(AfterLastTestMethodErroredSubscriber::class)]
#[CoversClass(AfterLastTestMethodFailedSubscriber::class)]
#[CoversClass(BeforeFirstTestMethodErroredSubscriber::class)]
#[CoversClass(BeforeFirstTestMethodFailedSubscriber::class)]
#[CoversClass(TestErroredSubscriber::class)]
#[CoversClass(TestFailedSubscriber::class)]
#[CoversClass(TestPreparationStartedSubscriber::class)]
#[CoversClass(TestPrintedUnexpectedOutputSubscriber::class)]
#[CoversClass(TestRunnerExecutionFinishedSubscriber::class)]
#[Medium]
final class ProgressPrinterTest extends TestCase
{
    /**
     * @return array<string,array{0: string, 1: Closure(ProgressPrinter): void}>
     */
    public static function provider(): array
    {
        return [
            'errored test' => [
                'errored_test.txt',
                static function (ProgressPrinter $printer): void
                {
                    $printer->testErrored(
                        new Errored(
                            self::telemetryInfo(),
                            self::testMethod(),
                            ThrowableBuilder::from(new Exception('message')),
                        ),
                    );
                },
            ],

            'errored test with previous' => [
                'errored_test_with_previous.txt',
                static function (ProgressPrinter $printer): void
                {
                    $printer->testErrored(
                        new Errored(
                            self::telemetryInfo(),
                            self::testMethod(),
                            ThrowableBuilder::from(new RuntimeException('outer', 0, new LogicException('inner'))),
                        ),
                    );
                },
            ],

            'failed test' => [
                'failed_test.txt',
                static function (ProgressPrinter $printer): void
                {
                    $printer->testFailed(
                        new Failed(
                            self::telemetryInfo(),
                            self::testMethod(),
                            ThrowableBuilder::from(new ExpectationFailedException('Failed asserting that false is true.')),
                            null,
                        ),
                    );
                },
            ],

            'failed test with assertion error' => [
                'failed_test_with_assertion_error.txt',
                static function (ProgressPrinter $printer): void
                {
                    $printer->testFailed(
                        new Failed(
                            self::telemetryInfo(),
                            self::testMethod(),
                            ThrowableBuilder::from(new AssertionError('Failed asserting that false is true.')),
                            null,
                        ),
                    );
                },
            ],

            'failed test with data provider' => [
                'failed_test_with_data_provider.txt',
                static function (ProgressPrinter $printer): void
                {
                    $printer->testFailed(
                        new Failed(
                            self::telemetryInfo(),
                            self::testMethodWithDataProvider(),
                            ThrowableBuilder::from(new ExpectationFailedException('Failed asserting that 1 matches expected 2.')),
                            null,
                        ),
                    );
                },
            ],

            'before first test method errored' => [
                'before_first_test_method_errored.txt',
                static function (ProgressPrinter $printer): void
                {
                    $printer->beforeFirstTestMethodErrored(
                        new BeforeFirstTestMethodErrored(
                            self::telemetryInfo(),
                            'FooTest',
                            new ClassMethod('FooTest', 'setUpBeforeClass'),
                            ThrowableBuilder::from(new Exception('message')),
                        ),
                    );
                },
            ],

            'before first test method failed' => [
                'before_first_test_method_failed.txt',
                static function (ProgressPrinter $printer): void
                {
                    $printer->beforeFirstTestMethodFailed(
                        new BeforeFirstTestMethodFailed(
                            self::telemetryInfo(),
                            'FooTest',
                            new ClassMethod('FooTest', 'setUpBeforeClass'),
                            ThrowableBuilder::from(new ExpectationFailedException('Failed asserting that false is true.')),
                        ),
                    );
                },
            ],

            'after last test method errored' => [
                'after_last_test_method_errored.txt',
                static function (ProgressPrinter $printer): void
                {
                    $printer->afterLastTestMethodErrored(
                        new AfterLastTestMethodErrored(
                            self::telemetryInfo(),
                            'FooTest',
                            new ClassMethod('FooTest', 'tearDownAfterClass'),
                            ThrowableBuilder::from(new Exception('message')),
                        ),
                    );
                },
            ],

            'after last test method failed' => [
                'after_last_test_method_failed.txt',
                static function (ProgressPrinter $printer): void
                {
                    $printer->afterLastTestMethodFailed(
                        new AfterLastTestMethodFailed(
                            self::telemetryInfo(),
                            'FooTest',
                            new ClassMethod('FooTest', 'tearDownAfterClass'),
                            ThrowableBuilder::from(new ExpectationFailedException('Failed asserting that false is true.')),
                        ),
                    );
                },
            ],
        ];
    }

    /**
     * @return array<string,array{0: class-string<Subscriber>, 1: Event}>
     */
    public static function subscriberProvider(): array
    {
        return [
            'test errored' => [
                TestErroredSubscriber::class,
                new Errored(
                    self::telemetryInfo(),
                    self::testMethod(),
                    ThrowableBuilder::from(new Exception('message')),
                ),
            ],

            'test failed' => [
                TestFailedSubscriber::class,
                new Failed(
                    self::telemetryInfo(),
                    self::testMethod(),
                    ThrowableBuilder::from(new ExpectationFailedException('Failed asserting that false is true.')),
                    null,
                ),
            ],

            'before first test method errored' => [
                BeforeFirstTestMethodErroredSubscriber::class,
                new BeforeFirstTestMethodErrored(
                    self::telemetryInfo(),
                    'FooTest',
                    new ClassMethod('FooTest', 'setUpBeforeClass'),
                    ThrowableBuilder::from(new Exception('message')),
                ),
            ],

            'before first test method failed' => [
                BeforeFirstTestMethodFailedSubscriber::class,
                new BeforeFirstTestMethodFailed(
                    self::telemetryInfo(),
                    'FooTest',
                    new ClassMethod('FooTest', 'setUpBeforeClass'),
                    ThrowableBuilder::from(new ExpectationFailedException('Failed asserting that false is true.')),
                ),
            ],

            'after last test method errored' => [
                AfterLastTestMethodErroredSubscriber::class,
                new AfterLastTestMethodErrored(
                    self::telemetryInfo(),
                    'FooTest',
                    new ClassMethod('FooTest', 'tearDownAfterClass'),
                    ThrowableBuilder::from(new Exception('message')),
                ),
            ],

            'after last test method failed' => [
                AfterLastTestMethodFailedSubscriber::class,
                new AfterLastTestMethodFailed(
                    self::telemetryInfo(),
                    'FooTest',
                    new ClassMethod('FooTest', 'tearDownAfterClass'),
                    ThrowableBuilder::from(new ExpectationFailedException('Failed asserting that false is true.')),
                ),
            ],
        ];
    }

    /**
     * @param class-string<Subscriber> $subscriberClassName
     */
    #[DataProvider('subscriberProvider')]
    public function testSubscriberForwardsEventToProgressPrinter(string $subscriberClassName, Event $event): void
    {
        $printer         = $this->printer();
        $progressPrinter = new ProgressPrinter($printer, new EventFacade, true);

        $subscriber = new $subscriberClassName($progressPrinter);

        $subscriber->notify($event);

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertNotSame('', $printer->buffer());
    }

    #[DataProvider('provider')]
    public function testPrintsExpectedOutputForEvent(string $expectationFile, Closure $trigger): void
    {
        $printer         = $this->printer();
        $progressPrinter = new ProgressPrinter($printer, new EventFacade, true);

        $trigger($progressPrinter);

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertStringMatchesFormatFile(
            __DIR__ . '/expectations/progress/' . $expectationFile,
            $printer->buffer(),
        );
    }

    public function testPrintsUnexpectedOutputAsRecordAttributedToCurrentTest(): void
    {
        $printer         = $this->printer();
        $progressPrinter = new ProgressPrinter($printer, new EventFacade, true);

        new TestPreparationStartedSubscriber($progressPrinter)->notify(
            new PreparationStarted(self::telemetryInfo(), self::testMethod()),
        );

        new TestPrintedUnexpectedOutputSubscriber($progressPrinter)->notify(
            new PrintedUnexpectedOutput(self::telemetryInfo(), "unexpected output\n"),
        );

        new TestRunnerExecutionFinishedSubscriber($progressPrinter)->notify(
            new ExecutionFinished(self::telemetryInfo()),
        );

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertSame(
            PHP_EOL . '--- OUTPUT: FooTest::testBar' . PHP_EOL . 'unexpected output' . PHP_EOL . PHP_EOL,
            $printer->buffer(),
        );
    }

    public function testPrintsOnlyHeaderWhenUnexpectedOutputConsistsOfLineFeedsOnly(): void
    {
        $printer         = $this->printer();
        $progressPrinter = new ProgressPrinter($printer, new EventFacade, true);

        $progressPrinter->testPreparationStarted(new PreparationStarted(self::telemetryInfo(), self::testMethod()));
        $progressPrinter->testPrintedUnexpectedOutput(new PrintedUnexpectedOutput(self::telemetryInfo(), "\n\n"));

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertSame(
            PHP_EOL . '--- OUTPUT: FooTest::testBar' . PHP_EOL,
            $printer->buffer(),
        );
    }

    public function testDoesNotPrintUnexpectedOutputWhenItIsReportedAsRisky(): void
    {
        $printer         = $this->printer();
        $progressPrinter = new ProgressPrinter($printer, new EventFacade, false);

        $progressPrinter->testPreparationStarted(new PreparationStarted(self::telemetryInfo(), self::testMethod()));
        $progressPrinter->testPrintedUnexpectedOutput(new PrintedUnexpectedOutput(self::telemetryInfo(), "unexpected output\n"));
        $progressPrinter->testRunnerExecutionFinished();

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertSame('', $printer->buffer());
    }

    public function testDoesNotPrintSeparatorWhenNoRecordsWerePrinted(): void
    {
        $printer         = $this->printer();
        $progressPrinter = new ProgressPrinter($printer, new EventFacade, true);

        $progressPrinter->testRunnerExecutionFinished();

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertSame('', $printer->buffer());
    }

    public function testPrintsSeparatorAfterRecords(): void
    {
        $printer         = $this->printer();
        $progressPrinter = new ProgressPrinter($printer, new EventFacade, true);

        $progressPrinter->testFailed(
            new Failed(
                self::telemetryInfo(),
                self::testMethod(),
                ThrowableBuilder::from(new ExpectationFailedException('Failed asserting that false is true.')),
                null,
            ),
        );

        $progressPrinter->testRunnerExecutionFinished();

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertStringMatchesFormat(
            file_get_contents(__DIR__ . '/expectations/progress/failed_test.txt') . PHP_EOL,
            $printer->buffer(),
        );
    }

    private function printer(): Printer
    {
        return new class implements Printer
        {
            private string $buffer = '';

            public function print(string $buffer): void
            {
                $this->buffer .= $buffer;
            }

            public function flush(): void
            {
            }

            public function buffer(): string
            {
                return $this->buffer;
            }
        };
    }

    private static function testMethod(): TestMethod
    {
        return new TestMethod(
            'FooTest',
            'testBar',
            'FooTest.php',
            1,
            TestDoxBuilder::fromClassNameAndMethodName('Foo', 'bar'),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([]),
        );
    }

    private static function testMethodWithDataProvider(): TestMethod
    {
        return new TestMethod(
            'FooTest',
            'testBar',
            'FooTest.php',
            1,
            TestDoxBuilder::fromClassNameAndMethodName('Foo', 'bar'),
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([
                DataFromDataProvider::from(
                    'negative numbers',
                    'a]',
                    '#2 (negative numbers)',
                ),
            ]),
        );
    }

    private static function telemetryInfo(): Info
    {
        return new Info(
            new Snapshot(
                HRTime::fromSecondsAndNanoseconds(...hrtime(false)),
                MemoryUsage::fromBytes(1000),
                MemoryUsage::fromBytes(2000),
                new GarbageCollectorStatus(0, 0, 0, 0, 0.0, 0.0, 0.0, 0.0, false, false, false, 0),
                CpuTime::fromSecondsAndNanoseconds(0, 0),
                CpuTime::fromSecondsAndNanoseconds(0, 0),
                CpuTime::fromSecondsAndNanoseconds(0, 0),
            ),
            Duration::fromSecondsAndNanoseconds(123, 456),
            MemoryUsage::fromBytes(2000),
            Duration::fromSecondsAndNanoseconds(234, 567),
            MemoryUsage::fromBytes(3000),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
        );
    }
}
