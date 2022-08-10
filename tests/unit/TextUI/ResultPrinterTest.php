<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI;

use function hrtime;
use Exception;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErrorTriggered;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\NoticeTriggered;
use PHPUnit\Event\Test\PassedWithWarning;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpErrorTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\TestDataCollection;
use PHPUnit\Event\TestRunner\WarningTriggered as TestRunnerWarningTriggered;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\Util\Printer;

#[CoversClass(ResultPrinter::class)]
#[Medium]
final class ResultPrinterTest extends TestCase
{
    #[DataProvider('provider')]
    public function testPrintsExpectedOutputForTestResultObject(string $expectationFile, TestResult $result): void
    {
        $printer = $this->printer();

        $resultPrinter = new ResultPrinter(
            $printer,
            true,
            true,
            false,
            false
        );

        $resultPrinter->printResult($result);
        $resultPrinter->flush();

        $this->assertStringMatchesFormatFile(
            $expectationFile,
            $printer->buffer()
        );
    }

    /**
     * @psalm-return array<string,array{0: string, 1: TestResult}>
     */
    public function provider(): array
    {
        return [
            'successful test' => [
                __DIR__ . '/expectations/successful_test.txt',
                $this->createTestResult(),
            ],

            'errored test' => [
                __DIR__ . '/expectations/errored_test.txt',
                $this->createTestResult(
                    testErroredEvents: [
                        $this->erroredTest(),
                    ]
                ),
            ],

            'failed test' => [
                __DIR__ . '/expectations/failed_test.txt',
                $this->createTestResult(
                    testFailedEvents: [
                        $this->failedTest(),
                    ]
                ),
            ],

            'incomplete test' => [
                __DIR__ . '/expectations/incomplete_test.txt',
                $this->createTestResult(
                    testMarkedIncompleteEvents: [
                        $this->incompleteTest(),
                    ]
                ),
            ],

            'skipped test' => [
                __DIR__ . '/expectations/skipped_test.txt',
                $this->createTestResult(
                    testSkippedEvents: [
                        $this->skippedTest(),
                    ]
                ),
            ],

            'risky test with single-line message' => [
                __DIR__ . '/expectations/risky_test_single_line_message.txt',
                $this->createTestResult(
                    testConsideredRiskyEvents: [
                        'Foo::testBar' => [
                            $this->riskyTest('message'),
                        ],
                    ]
                ),
            ],

            'risky test with multiple reasons with single-line messages' => [
                __DIR__ . '/expectations/risky_test_with_multiple_reasons_with_single_line_messages.txt',
                $this->createTestResult(
                    testConsideredRiskyEvents: [
                        'Foo::testBar' => [
                            $this->riskyTest('message'),
                            $this->riskyTest('message'),
                        ],
                    ]
                ),
            ],

            'risky test with multiple reasons with multi-line messages' => [
                __DIR__ . '/expectations/risky_test_with_multiple_reasons_with_multi_line_messages.txt',
                $this->createTestResult(
                    testConsideredRiskyEvents: [
                        'Foo::testBar' => [
                            $this->riskyTest("message\nmessage\nmessage"),
                            $this->riskyTest("message\nmessage\nmessage"),
                        ],
                    ]
                ),
            ],
        ];
    }

    /**
     * @psalm-param list<BeforeFirstTestMethodErrored|Errored> $testErroredEvents
     * @psalm-param list<Failed> $testFailedEvents
     * @psalm-param list<PassedWithWarning> $testPassedWithWarningEvents
     * @psalm-param array<string,list<ConsideredRisky>> $testConsideredRiskyEvents
     * @psalm-param list<Skipped> $testSkippedEvents
     * @psalm-param list<MarkedIncomplete> $testMarkedIncompleteEvents
     * @psalm-param array<string,list<DeprecationTriggered>> $testTriggeredDeprecationEvents
     * @psalm-param array<string,list<PhpDeprecationTriggered>> $testTriggeredPhpDeprecationEvents
     * @psalm-param array<string,list<PhpunitDeprecationTriggered>> $testTriggeredPhpunitDeprecationEvents
     * @psalm-param array<string,list<ErrorTriggered>> $testTriggeredErrorEvents
     * @psalm-param array<string,list<PhpErrorTriggered>> $testTriggeredPhpErrorEvents
     * @psalm-param array<string,list<NoticeTriggered>> $testTriggeredNoticeEvents
     * @psalm-param array<string,list<PhpNoticeTriggered>> $testTriggeredPhpNoticeEvents
     * @psalm-param array<string,list<WarningTriggered>> $testTriggeredWarningEvents
     * @psalm-param array<string,list<PhpWarningTriggered>> $testTriggeredPhpWarningEvents
     * @psalm-param array<string,list<PhpunitWarningTriggered>> $testTriggeredPhpunitWarningEvents
     * @psalm-param list<TestRunnerWarningTriggered> $testRunnerTriggeredWarningEvents
     */
    private function createTestResult(int $numberOfTests = 1, int $numberOfTestsRun = 1, int $numberOfAssertions = 1, array $testErroredEvents = [], array $testFailedEvents = [], array $testPassedWithWarningEvents = [], array $testConsideredRiskyEvents = [], array $testSkippedEvents = [], array $testMarkedIncompleteEvents = [], array $testTriggeredDeprecationEvents = [], array $testTriggeredPhpDeprecationEvents = [], array $testTriggeredPhpunitDeprecationEvents = [], array $testTriggeredErrorEvents = [], array $testTriggeredPhpErrorEvents = [], array $testTriggeredNoticeEvents = [], array $testTriggeredPhpNoticeEvents = [], array $testTriggeredWarningEvents = [], array $testTriggeredPhpWarningEvents = [], array $testTriggeredPhpunitWarningEvents = [], array $testRunnerTriggeredWarningEvents = []): TestResult
    {
        return new TestResult(
            $numberOfTests,
            $numberOfTestsRun,
            $numberOfAssertions,
            $testErroredEvents,
            $testFailedEvents,
            $testPassedWithWarningEvents,
            $testConsideredRiskyEvents,
            $testSkippedEvents,
            $testMarkedIncompleteEvents,
            $testTriggeredDeprecationEvents,
            $testTriggeredPhpDeprecationEvents,
            $testTriggeredPhpunitDeprecationEvents,
            $testTriggeredErrorEvents,
            $testTriggeredPhpErrorEvents,
            $testTriggeredNoticeEvents,
            $testTriggeredPhpNoticeEvents,
            $testTriggeredWarningEvents,
            $testTriggeredPhpWarningEvents,
            $testTriggeredPhpunitWarningEvents,
            $testRunnerTriggeredWarningEvents
        );
    }

    private function erroredTest(): Errored
    {
        return new Errored(
            $this->telemetryInfo(),
            $this->testMethod(),
            Throwable::from(new Exception('message'))
        );
    }

    private function failedTest(): Failed
    {
        return new Failed(
            $this->telemetryInfo(),
            $this->testMethod(),
            Throwable::from(
                new ExpectationFailedException(
                    'Failed asserting that false is true.'
                )
            )
        );
    }

    private function incompleteTest(): MarkedIncomplete
    {
        return new MarkedIncomplete(
            $this->telemetryInfo(),
            $this->testMethod(),
            Throwable::from(new IncompleteTestError('message'))
        );
    }

    private function skippedTest(): Skipped
    {
        return new Skipped(
            $this->telemetryInfo(),
            $this->testMethod(),
            'message'
        );
    }

    private function riskyTest(string $message): ConsideredRisky
    {
        return new ConsideredRisky(
            $this->telemetryInfo(),
            $this->testMethod(),
            $message
        );
    }

    private function testMethod(): TestMethod
    {
        return new TestMethod(
            'FooTest',
            'testBar',
            'FooTest.php',
            1,
            MetadataCollection::fromArray([]),
            TestDataCollection::fromArray([])
        );
    }

    private function telemetryInfo(): Info
    {
        return new Info(
            new Snapshot(
                HRTime::fromSecondsAndNanoseconds(...hrtime(false)),
                MemoryUsage::fromBytes(1000),
                MemoryUsage::fromBytes(2000)
            ),
            Duration::fromSecondsAndNanoseconds(123, 456),
            MemoryUsage::fromBytes(2000),
            Duration::fromSecondsAndNanoseconds(234, 567),
            MemoryUsage::fromBytes(3000)
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
}
