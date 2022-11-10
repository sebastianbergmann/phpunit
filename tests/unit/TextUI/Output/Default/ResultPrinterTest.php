<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output\Default;

use function hrtime;
use Exception;
use PHPUnit\Event\Code\ClassMethod;
use PHPUnit\Event\Code\TestDox;
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
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitErrorTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Event\TestRunner\DeprecationTriggered as TestRunnerDeprecationTriggered;
use PHPUnit\Event\TestRunner\WarningTriggered as TestRunnerWarningTriggered;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\TextUI\Output\SummaryPrinter;
use PHPUnit\Util\Printer;

#[CoversClass(ResultPrinter::class)]
#[CoversClass(SummaryPrinter::class)]
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
            true,
            true,
            true,
            true,
            false
        );

        $resultPrinter->print($result);
        $resultPrinter->flush();

        $summaryPrinter = new SummaryPrinter(
            $printer,
            false
        );

        $summaryPrinter->print($result);

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
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
            'no tests' => [
                __DIR__ . '/expectations/no_tests.txt',
                $this->createTestResult(
                    numberOfTestsRun: 0
                ),
            ],

            'successful test without issues' => [
                __DIR__ . '/expectations/successful_test_without_issues.txt',
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
                        new MarkedIncomplete(
                            $this->telemetryInfo(),
                            $this->testMethod(),
                            Throwable::from(new IncompleteTestError('message'))
                        ),
                    ]
                ),
            ],

            'skipped test' => [
                __DIR__ . '/expectations/skipped_test.txt',
                $this->createTestResult(
                    testSkippedEvents: [
                        new Skipped(
                            $this->telemetryInfo(),
                            $this->testMethod(),
                            'message'
                        ),
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

            'errored test that is risky' => [
                __DIR__ . '/expectations/errored_test_that_is_risky.txt',
                $this->createTestResult(
                    testErroredEvents: [
                        $this->erroredTest(),
                    ],
                    testConsideredRiskyEvents: [
                        'Foo::testBar' => [
                            $this->riskyTest('message'),
                        ],
                    ]
                ),
            ],

            'failed test that is risky' => [
                __DIR__ . '/expectations/failed_test_that_is_risky.txt',
                $this->createTestResult(
                    testFailedEvents: [
                        $this->failedTest(),
                    ],
                    testConsideredRiskyEvents: [
                        'Foo::testBar' => [
                            $this->riskyTest('message'),
                        ],
                    ]
                ),
            ],

            'successful test that triggers deprecation' => [
                __DIR__ . '/expectations/successful_test_with_deprecation.txt',
                $this->createTestResult(
                    testTriggeredDeprecationEvents: [
                        'Foo::testBar' => [
                            new DeprecationTriggered(
                                $this->telemetryInfo(),
                                $this->testMethod(),
                                'message',
                                'file',
                                1
                            ),
                        ],
                    ]
                ),
            ],

            'successful test that triggers PHP deprecation' => [
                __DIR__ . '/expectations/successful_test_with_php_deprecation.txt',
                $this->createTestResult(
                    testTriggeredPhpDeprecationEvents: [
                        'Foo::testBar' => [
                            new PhpDeprecationTriggered(
                                $this->telemetryInfo(),
                                $this->testMethod(),
                                'message',
                                'file',
                                1
                            ),
                        ],
                    ]
                ),
            ],

            'successful test that triggers PHPUnit deprecation' => [
                __DIR__ . '/expectations/successful_test_with_phpunit_deprecation.txt',
                $this->createTestResult(
                    testTriggeredPhpunitDeprecationEvents: [
                        'Foo::testBar' => [
                            new PhpunitDeprecationTriggered(
                                $this->telemetryInfo(),
                                $this->testMethod(),
                                'message'
                            ),
                        ],
                    ]
                ),
            ],

            'successful test that triggers error' => [
                __DIR__ . '/expectations/successful_test_with_error.txt',
                $this->createTestResult(
                    testTriggeredErrorEvents: [
                        'Foo::testBar' => [
                            new ErrorTriggered(
                                $this->telemetryInfo(),
                                $this->testMethod(),
                                'message',
                                'file',
                                1
                            ),
                        ],
                    ]
                ),
            ],

            'successful test that triggers notice' => [
                __DIR__ . '/expectations/successful_test_with_notice.txt',
                $this->createTestResult(
                    testTriggeredNoticeEvents: [
                        'Foo::testBar' => [
                            new NoticeTriggered(
                                $this->telemetryInfo(),
                                $this->testMethod(),
                                'message',
                                'file',
                                1
                            ),
                        ],
                    ]
                ),
            ],

            'successful test that triggers PHP notice' => [
                __DIR__ . '/expectations/successful_test_with_php_notice.txt',
                $this->createTestResult(
                    testTriggeredPhpNoticeEvents: [
                        'Foo::testBar' => [
                            new PhpNoticeTriggered(
                                $this->telemetryInfo(),
                                $this->testMethod(),
                                'message',
                                'file',
                                1
                            ),
                        ],
                    ]
                ),
            ],

            'successful test that triggers warning' => [
                __DIR__ . '/expectations/successful_test_with_warning.txt',
                $this->createTestResult(
                    testTriggeredWarningEvents: [
                        'Foo::testBar' => [
                            new WarningTriggered(
                                $this->telemetryInfo(),
                                $this->testMethod(),
                                'message',
                                'file',
                                1
                            ),
                        ],
                    ]
                ),
            ],

            'successful test that triggers PHP warning' => [
                __DIR__ . '/expectations/successful_test_with_php_warning.txt',
                $this->createTestResult(
                    testTriggeredPhpWarningEvents: [
                        'Foo::testBar' => [
                            new PhpWarningTriggered(
                                $this->telemetryInfo(),
                                $this->testMethod(),
                                'message',
                                'file',
                                1
                            ),
                        ],
                    ]
                ),
            ],

            'test that triggers PHPUnit error' => [
                __DIR__ . '/expectations/test_with_phpunit_error.txt',
                $this->createTestResult(
                    numberOfTests: 0,
                    numberOfTestsRun: 0,
                    numberOfAssertions: 0,
                    testTriggeredPhpunitErrorEvents: [
                        'Foo::testBar' => [
                            new PhpunitErrorTriggered(
                                $this->telemetryInfo(),
                                $this->testMethod(),
                                'message'
                            ),
                        ],
                    ]
                ),
            ],

            'successful test that triggers PHPUnit warning' => [
                __DIR__ . '/expectations/successful_test_with_phpunit_warning.txt',
                $this->createTestResult(
                    testTriggeredPhpunitWarningEvents: [
                        'Foo::testBar' => [
                            new PhpunitWarningTriggered(
                                $this->telemetryInfo(),
                                $this->testMethod(),
                                'message'
                            ),
                        ],
                    ]
                ),
            ],

        ];
    }

    /**
     * @psalm-param list<BeforeFirstTestMethodErrored|Errored> $testErroredEvents
     * @psalm-param list<Failed> $testFailedEvents
     * @psalm-param array<string,list<ConsideredRisky>> $testConsideredRiskyEvents
     * @psalm-param list<Skipped> $testSkippedEvents
     * @psalm-param list<MarkedIncomplete> $testMarkedIncompleteEvents
     * @psalm-param array<string,list<DeprecationTriggered>> $testTriggeredDeprecationEvents
     * @psalm-param array<string,list<PhpDeprecationTriggered>> $testTriggeredPhpDeprecationEvents
     * @psalm-param array<string,list<PhpunitDeprecationTriggered>> $testTriggeredPhpunitDeprecationEvents
     * @psalm-param array<string,list<ErrorTriggered>> $testTriggeredErrorEvents
     * @psalm-param array<string,list<NoticeTriggered>> $testTriggeredNoticeEvents
     * @psalm-param array<string,list<PhpNoticeTriggered>> $testTriggeredPhpNoticeEvents
     * @psalm-param array<string,list<WarningTriggered>> $testTriggeredWarningEvents
     * @psalm-param array<string,list<PhpWarningTriggered>> $testTriggeredPhpWarningEvents
     * @psalm-param array<string,list<PhpunitErrorTriggered>> $testTriggeredPhpunitErrorEvents
     * @psalm-param array<string,list<PhpunitWarningTriggered>> $testTriggeredPhpunitWarningEvents
     * @psalm-param list<TestRunnerDeprecationTriggered> $testRunnerTriggeredDeprecationEvents
     * @psalm-param list<TestRunnerWarningTriggered> $testRunnerTriggeredWarningEvents
     */
    private function createTestResult(int $numberOfTests = 1, int $numberOfTestsRun = 1, int $numberOfAssertions = 1, array $testErroredEvents = [], array $testFailedEvents = [], array $testConsideredRiskyEvents = [], array $testSkippedEvents = [], array $testMarkedIncompleteEvents = [], array $testTriggeredDeprecationEvents = [], array $testTriggeredPhpDeprecationEvents = [], array $testTriggeredPhpunitDeprecationEvents = [], array $testTriggeredErrorEvents = [], array $testTriggeredNoticeEvents = [], array $testTriggeredPhpNoticeEvents = [], array $testTriggeredWarningEvents = [], array $testTriggeredPhpWarningEvents = [], array $testTriggeredPhpunitErrorEvents = [], array $testTriggeredPhpunitWarningEvents = [], array $testRunnerTriggeredDeprecationEvents = [], array $testRunnerTriggeredWarningEvents = []): TestResult
    {
        return new TestResult(
            $numberOfTests,
            $numberOfTestsRun,
            $numberOfAssertions,
            $testErroredEvents,
            $testFailedEvents,
            $testConsideredRiskyEvents,
            $testSkippedEvents,
            $testMarkedIncompleteEvents,
            $testTriggeredDeprecationEvents,
            $testTriggeredPhpDeprecationEvents,
            $testTriggeredPhpunitDeprecationEvents,
            $testTriggeredErrorEvents,
            $testTriggeredNoticeEvents,
            $testTriggeredPhpNoticeEvents,
            $testTriggeredWarningEvents,
            $testTriggeredPhpWarningEvents,
            $testTriggeredPhpunitErrorEvents,
            $testTriggeredPhpunitWarningEvents,
            $testRunnerTriggeredDeprecationEvents,
            $testRunnerTriggeredWarningEvents,
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
            new TestDox(
                'Foo',
                'Bar',
                'Bar',
            ),
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
            MemoryUsage::fromBytes(3000),
            new ClassMethod(__CLASS__, __METHOD__)
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
