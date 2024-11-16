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

use const PHP_OS;
use function hrtime;
use function stripos;
use Exception;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitErrorTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\Skipped as TestSkipped;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Event\TestRunner\DeprecationTriggered as TestRunnerDeprecationTriggered;
use PHPUnit\Event\TestRunner\WarningTriggered as TestRunnerWarningTriggered;
use PHPUnit\Event\TestSuite\Skipped as TestSuiteSkipped;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\IncompleteTestError;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\TestRunner\TestResult\Issues\Issue;
use PHPUnit\TestRunner\TestResult\TestResult;
use PHPUnit\TextUI\Output\Printer;
use PHPUnit\TextUI\Output\SummaryPrinter;

#[CoversClass(ResultPrinter::class)]
#[CoversClass(SummaryPrinter::class)]
#[Medium]
final class ResultPrinterTest extends TestCase
{
    /**
     * @return array<string,array{0: string, 1: TestResult}>
     */
    public static function provider(): array
    {
        return [
            'no tests' => [
                __DIR__ . '/expectations/no_tests.txt',
                self::createTestResult(
                    numberOfTestsRun: 0,
                ),
            ],

            'successful test without issues' => [
                __DIR__ . '/expectations/successful_test_without_issues.txt',
                self::createTestResult(),
            ],

            'errored test' => [
                __DIR__ . '/expectations/errored_test.txt',
                self::createTestResult(
                    testErroredEvents: [
                        self::erroredTest(),
                    ],
                ),
            ],

            'failed test' => [
                __DIR__ . '/expectations/failed_test.txt',
                self::createTestResult(
                    testFailedEvents: [
                        self::failedTest(),
                    ],
                ),
            ],

            'incomplete test' => [
                __DIR__ . '/expectations/incomplete_test.txt',
                self::createTestResult(
                    testMarkedIncompleteEvents: [
                        new MarkedIncomplete(
                            self::telemetryInfo(),
                            self::testMethod(),
                            ThrowableBuilder::from(new IncompleteTestError('message')),
                        ),
                    ],
                ),
            ],

            'skipped test' => [
                __DIR__ . '/expectations/skipped_test.txt',
                self::createTestResult(
                    testSkippedEvents: [
                        new TestSkipped(
                            self::telemetryInfo(),
                            self::testMethod(),
                            'message',
                        ),
                    ],
                ),
            ],

            'risky test with single-line message' => [
                __DIR__ . '/expectations/risky_test_single_line_message.txt',
                self::createTestResult(
                    testConsideredRiskyEvents: [
                        'Foo::testBar' => [
                            self::riskyTest('message'),
                        ],
                    ],
                ),
            ],

            'risky test with multiple reasons with single-line messages' => [
                __DIR__ . '/expectations/risky_test_with_multiple_reasons_with_single_line_messages.txt',
                self::createTestResult(
                    testConsideredRiskyEvents: [
                        'Foo::testBar' => [
                            self::riskyTest('message'),
                            self::riskyTest('message'),
                        ],
                    ],
                ),
            ],

            'risky test with multiple reasons with multi-line messages' => [
                (stripos(PHP_OS, 'WIN') === 0) ?
                    __DIR__ . '/expectations/risky_test_with_multiple_reasons_with_multi_line_messages_windows.txt' :
                    __DIR__ . '/expectations/risky_test_with_multiple_reasons_with_multi_line_messages.txt',
                self::createTestResult(
                    testConsideredRiskyEvents: [
                        'Foo::testBar' => [
                            self::riskyTest("message\nmessage\nmessage"),
                            self::riskyTest("message\nmessage\nmessage"),
                        ],
                    ],
                ),
            ],

            'errored test that is risky' => [
                __DIR__ . '/expectations/errored_test_that_is_risky.txt',
                self::createTestResult(
                    testErroredEvents: [
                        self::erroredTest(),
                    ],
                    testConsideredRiskyEvents: [
                        'Foo::testBar' => [
                            self::riskyTest('message'),
                        ],
                    ],
                ),
            ],

            'failed test that is risky' => [
                __DIR__ . '/expectations/failed_test_that_is_risky.txt',
                self::createTestResult(
                    testFailedEvents: [
                        self::failedTest(),
                    ],
                    testConsideredRiskyEvents: [
                        'Foo::testBar' => [
                            self::riskyTest('message'),
                        ],
                    ],
                ),
            ],

            'successful test that triggers deprecation (do not display stack trace)' => [
                __DIR__ . '/expectations/successful_test_with_deprecation.txt',
                self::createTestResult(
                    deprecations: [
                        Issue::from(
                            'Foo.php',
                            1,
                            'message',
                            self::testMethod(),
                        ),
                    ],
                ),
            ],

            'successful test that triggers deprecation (display stack trace)' => [
                __DIR__ . '/expectations/successful_test_with_deprecation_with_stack_trace.txt',
                self::createTestResult(
                    deprecations: [
                        Issue::from(
                            'Foo.php',
                            1,
                            'message',
                            self::testMethod(),
                            '/path/to/file.php:1234',
                        ),
                    ],
                ),
                true,
            ],

            'successful test that triggers PHP deprecation' => [
                __DIR__ . '/expectations/successful_test_with_php_deprecation.txt',
                self::createTestResult(
                    phpDeprecations: [
                        Issue::from(
                            'Foo.php',
                            1,
                            'message',
                            self::testMethod(),
                        ),
                        Issue::from(
                            'Foo.php',
                            2,
                            'another message',
                            self::testMethod(),
                        ),
                    ],
                ),
            ],

            'successful test that triggers the same PHP deprecation multiple times' => [
                __DIR__ . '/expectations/successful_test_with_php_deprecation_multiple.txt',
                self::createTestResult(
                    phpDeprecations: self::samePhpDeprecationsTriggeredTwice(),
                ),
            ],

            'successful test that triggers PHPUnit deprecation' => [
                __DIR__ . '/expectations/successful_test_with_phpunit_deprecation.txt',
                self::createTestResult(
                    testTriggeredPhpunitDeprecationEvents: [
                        'Foo::testBar' => [
                            new PhpunitDeprecationTriggered(
                                self::telemetryInfo(),
                                self::testMethod(),
                                'message',
                            ),
                        ],
                    ],
                    testRunnerTriggeredDeprecationEvents: [
                        new TestRunnerDeprecationTriggered(
                            self::telemetryInfo(),
                            'message',
                        ),
                    ],
                ),
            ],

            'successful test that triggers error' => [
                __DIR__ . '/expectations/successful_test_with_error.txt',
                self::createTestResult(
                    errors: [
                        Issue::from(
                            'Foo.php',
                            1,
                            'message',
                            self::testMethod(),
                        ),
                    ],
                ),
            ],

            'successful test that triggers notice' => [
                __DIR__ . '/expectations/successful_test_with_notice.txt',
                self::createTestResult(
                    notices: [
                        Issue::from(
                            'Foo.php',
                            1,
                            'message',
                            self::testMethod(),
                        ),
                    ],
                ),
            ],

            'successful test that triggers PHP notice' => [
                __DIR__ . '/expectations/successful_test_with_php_notice.txt',
                self::createTestResult(
                    phpNotices: [
                        Issue::from(
                            'Foo.php',
                            1,
                            'message',
                            self::testMethod(),
                        ),
                    ],
                ),
            ],

            'successful test that triggers warning' => [
                __DIR__ . '/expectations/successful_test_with_warning.txt',
                self::createTestResult(
                    warnings: [
                        Issue::from(
                            'Foo.php',
                            1,
                            'message',
                            self::testMethod(),
                        ),
                    ],
                ),
            ],

            'successful test that triggers PHP warning' => [
                __DIR__ . '/expectations/successful_test_with_php_warning.txt',
                self::createTestResult(
                    phpWarnings: [
                        Issue::from(
                            'Foo.php',
                            1,
                            'message',
                            self::testMethod(),
                        ),
                    ],
                ),
            ],

            'test that triggers PHPUnit error' => [
                __DIR__ . '/expectations/test_with_phpunit_error.txt',
                self::createTestResult(
                    numberOfTests: 0,
                    numberOfTestsRun: 0,
                    numberOfAssertions: 0,
                    testTriggeredPhpunitErrorEvents: [
                        'Foo::testBar' => [
                            new PhpunitErrorTriggered(
                                self::telemetryInfo(),
                                self::testMethod(),
                                'message',
                            ),
                        ],
                    ],
                ),
            ],

            'successful test that triggers PHPUnit warning' => [
                __DIR__ . '/expectations/successful_test_with_phpunit_warning.txt',
                self::createTestResult(
                    testTriggeredPhpunitWarningEvents: [
                        'Foo::testBar' => [
                            new PhpunitWarningTriggered(
                                self::telemetryInfo(),
                                self::testMethod(),
                                'message',
                            ),
                        ],
                    ],
                ),
            ],

            'successful test that triggers baseline-ignored issue' => [
                __DIR__ . '/expectations/successful_test_with_baseline_ignored_issue.txt',
                self::createTestResult(
                    numberOfIssuesIgnoredByBaseline: 1,
                ),
            ],

            'successful test that triggers baseline-ignored issues' => [
                __DIR__ . '/expectations/successful_test_with_baseline_ignored_issues.txt',
                self::createTestResult(
                    numberOfIssuesIgnoredByBaseline: 2,
                ),
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testPrintsExpectedOutputForTestResultObject(string $expectationFile, TestResult $result, bool $stackTraceForDeprecations = false): void
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
            true,
            true,
            true,
            true,
            true,
            true,
            false,
        );

        $resultPrinter->print($result, $stackTraceForDeprecations);

        $summaryPrinter = new SummaryPrinter(
            $printer,
            false,
        );

        $summaryPrinter->print($result);

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertStringMatchesFormatFile(
            $expectationFile,
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

    /**
     * @param list<BeforeFirstTestMethodErrored|Errored>      $testErroredEvents
     * @param list<Failed>                                    $testFailedEvents
     * @param array<string,list<ConsideredRisky>>             $testConsideredRiskyEvents
     * @param list<TestSuiteSkipped>                          $testSuiteSkippedEvents
     * @param list<TestSkipped>                               $testSkippedEvents
     * @param list<MarkedIncomplete>                          $testMarkedIncompleteEvents
     * @param list<Issue>                                     $deprecations
     * @param list<Issue>                                     $phpDeprecations
     * @param array<string,list<PhpunitDeprecationTriggered>> $testTriggeredPhpunitDeprecationEvents
     * @param list<Issue>                                     $errors
     * @param list<Issue>                                     $notices
     * @param list<Issue>                                     $phpNotices
     * @param list<Issue>                                     $warnings
     * @param list<Issue>                                     $phpWarnings
     * @param array<string,list<PhpunitErrorTriggered>>       $testTriggeredPhpunitErrorEvents
     * @param array<string,list<PhpunitWarningTriggered>>     $testTriggeredPhpunitWarningEvents
     * @param list<TestRunnerDeprecationTriggered>            $testRunnerTriggeredDeprecationEvents
     * @param list<TestRunnerWarningTriggered>                $testRunnerTriggeredWarningEvents
     */
    private static function createTestResult(int $numberOfTests = 1, int $numberOfTestsRun = 1, int $numberOfAssertions = 1, array $testErroredEvents = [], array $testFailedEvents = [], array $testConsideredRiskyEvents = [], array $testSuiteSkippedEvents = [], array $testSkippedEvents = [], array $testMarkedIncompleteEvents = [], array $deprecations = [], array $phpDeprecations = [], array $testTriggeredPhpunitDeprecationEvents = [], array $errors = [], array $notices = [], array $phpNotices = [], array $warnings = [], array $phpWarnings = [], array $testTriggeredPhpunitErrorEvents = [], array $testTriggeredPhpunitWarningEvents = [], array $testRunnerTriggeredDeprecationEvents = [], array $testRunnerTriggeredWarningEvents = [], int $numberOfIssuesIgnoredByBaseline = 0): TestResult
    {
        return new TestResult(
            $numberOfTests,
            $numberOfTestsRun,
            $numberOfAssertions,
            $testErroredEvents,
            $testFailedEvents,
            $testConsideredRiskyEvents,
            $testSuiteSkippedEvents,
            $testSkippedEvents,
            $testMarkedIncompleteEvents,
            $testTriggeredPhpunitDeprecationEvents,
            $testTriggeredPhpunitErrorEvents,
            $testTriggeredPhpunitWarningEvents,
            $testRunnerTriggeredDeprecationEvents,
            $testRunnerTriggeredWarningEvents,
            $errors,
            $deprecations,
            $notices,
            $warnings,
            $phpDeprecations,
            $phpNotices,
            $phpWarnings,
            $numberOfIssuesIgnoredByBaseline,
        );
    }

    private static function erroredTest(): Errored
    {
        return new Errored(
            self::telemetryInfo(),
            self::testMethod(),
            ThrowableBuilder::from(new Exception('message')),
        );
    }

    private static function failedTest(): Failed
    {
        return new Failed(
            self::telemetryInfo(),
            self::testMethod(),
            ThrowableBuilder::from(
                new ExpectationFailedException(
                    'Failed asserting that false is true.',
                ),
            ),
            null,
        );
    }

    private static function riskyTest(string $message): ConsideredRisky
    {
        return new ConsideredRisky(
            self::telemetryInfo(),
            self::testMethod(),
            $message,
        );
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

    private static function telemetryInfo(): Info
    {
        return new Info(
            new Snapshot(
                HRTime::fromSecondsAndNanoseconds(...hrtime(false)),
                MemoryUsage::fromBytes(1000),
                MemoryUsage::fromBytes(2000),
                new GarbageCollectorStatus(0, 0, 0, 0, 0.0, 0.0, 0.0, 0.0, false, false, false, 0),
            ),
            Duration::fromSecondsAndNanoseconds(123, 456),
            MemoryUsage::fromBytes(2000),
            Duration::fromSecondsAndNanoseconds(234, 567),
            MemoryUsage::fromBytes(3000),
        );
    }

    private static function samePhpDeprecationsTriggeredTwice(): array
    {
        $issue = Issue::from(
            'Foo.php',
            1,
            'message',
            self::testMethod(),
        );

        $issue->triggeredBy(self::testMethod());

        return [$issue];
    }
}
