<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TextUI\Output\Compact;

use function hrtime;
use Exception;
use PHPUnit\Event\Code\ClassMethod;
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
use PHPUnit\Event\Code\Phpt;
use PHPUnit\Event\Code\TestDoxBuilder;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Telemetry\CpuTime;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\BeforeFirstTestMethodFailed;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitErrorTriggered;
use PHPUnit\Event\Test\PhpunitNoticeTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\Skipped as TestSkipped;
use PHPUnit\Event\TestData\DataFromDataProvider;
use PHPUnit\Event\TestData\TestDataCollection;
use PHPUnit\Event\TestRunner\DeprecationTriggered as TestRunnerDeprecationTriggered;
use PHPUnit\Event\TestRunner\ErrorTriggered as TestRunnerIssueErrorTriggered;
use PHPUnit\Event\TestRunner\Issue\DeprecationTriggered as TestRunnerIssueDeprecationTriggered;
use PHPUnit\Event\TestRunner\Issue\NoticeTriggered as TestRunnerIssueNoticeTriggered;
use PHPUnit\Event\TestRunner\Issue\WarningTriggered as TestRunnerIssueWarningTriggered;
use PHPUnit\Event\TestRunner\NoticeTriggered as TestRunnerNoticeTriggered;
use PHPUnit\Event\TestRunner\PhpDeprecationTriggered as TestRunnerIssuePhpDeprecationTriggered;
use PHPUnit\Event\TestRunner\PhpNoticeTriggered as TestRunnerIssuePhpNoticeTriggered;
use PHPUnit\Event\TestRunner\PhpWarningTriggered as TestRunnerIssuePhpWarningTriggered;
use PHPUnit\Event\TestRunner\WarningTriggered as TestRunnerWarningTriggered;
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

#[CoversClass(ResultPrinter::class)]
#[Medium]
final class ResultPrinterTest extends TestCase
{
    /**
     * @return array<string,array{0: string, 1: TestResult, 2: bool, 3: bool, 4: bool, 5: bool, 6: bool, 7: bool}>
     */
    public static function provider(): array
    {
        return [
            'no tests' => [
                'no_tests.txt',
                self::createTestResult(
                    numberOfTestsRun: 0,
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'successful test without issues' => [
                'successful_test_without_issues.txt',
                self::createTestResult(),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'errored test' => [
                'errored_test.txt',
                self::createTestResult(
                    testErroredEvents: [
                        self::erroredTest(),
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'failed test' => [
                'failed_test.txt',
                self::createTestResult(
                    testFailedEvents: [
                        self::failedTest(),
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'risky test' => [
                'risky_test.txt',
                self::createTestResult(
                    testConsideredRiskyEvents: [
                        'Foo::testBar' => [
                            self::riskyTest('message'),
                        ],
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'errored test that is risky' => [
                'errored_test_that_is_risky.txt',
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
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'failed test that is risky' => [
                'failed_test_that_is_risky.txt',
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
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'incomplete test displayed' => [
                'incomplete_test_displayed.txt',
                self::createTestResult(
                    testMarkedIncompleteEvents: [
                        new MarkedIncomplete(
                            self::telemetryInfo(),
                            self::testMethod(),
                            ThrowableBuilder::from(new IncompleteTestError('message')),
                        ),
                    ],
                ),
                true,
                false,
                false,
                false,
                false,
                false,
            ],

            'incomplete test not displayed' => [
                'incomplete_test_not_displayed.txt',
                self::createTestResult(
                    testMarkedIncompleteEvents: [
                        new MarkedIncomplete(
                            self::telemetryInfo(),
                            self::testMethod(),
                            ThrowableBuilder::from(new IncompleteTestError('message')),
                        ),
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'skipped test displayed' => [
                'skipped_test_displayed.txt',
                self::createTestResult(
                    testSkippedEvents: [
                        new TestSkipped(
                            self::telemetryInfo(),
                            self::testMethod(),
                            'message',
                        ),
                    ],
                ),
                false,
                true,
                false,
                false,
                false,
                false,
            ],

            'skipped test not displayed' => [
                'skipped_test_not_displayed.txt',
                self::createTestResult(
                    testSkippedEvents: [
                        new TestSkipped(
                            self::telemetryInfo(),
                            self::testMethod(),
                            'message',
                        ),
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'deprecation displayed' => [
                'deprecation_displayed.txt',
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
                false,
                false,
                true,
                false,
                false,
                false,
            ],

            'deprecation not displayed' => [
                'deprecation_not_displayed.txt',
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
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'warning displayed' => [
                'warning_displayed.txt',
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
                false,
                false,
                false,
                false,
                false,
                true,
            ],

            'notice displayed' => [
                'notice_displayed.txt',
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
                false,
                false,
                false,
                false,
                true,
                false,
            ],

            'test-triggered error displayed' => [
                'test_triggered_error_displayed.txt',
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
                false,
                false,
                false,
                true,
                false,
                false,
            ],

            'error in before-first-test-method hook' => [
                'before_first_test_method_errored.txt',
                self::createTestResult(
                    testErroredEvents: [
                        new BeforeFirstTestMethodErrored(
                            self::telemetryInfo(),
                            'FooTest',
                            new ClassMethod('FooTest', 'setUpBeforeClass'),
                            ThrowableBuilder::from(new Exception('message')),
                        ),
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'failure in before-first-test-method hook' => [
                'before_first_test_method_failed.txt',
                self::createTestResult(
                    testFailedEvents: [
                        new BeforeFirstTestMethodFailed(
                            self::telemetryInfo(),
                            'FooTest',
                            new ClassMethod('FooTest', 'setUpBeforeClass'),
                            ThrowableBuilder::from(
                                new ExpectationFailedException(
                                    'AssertionError: Failed asserting that false is true.',
                                ),
                            ),
                        ),
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'error with chained previous exception' => [
                'errored_test_with_previous.txt',
                self::createTestResult(
                    testErroredEvents: [
                        new Errored(
                            self::telemetryInfo(),
                            self::testMethod(),
                            new Throwable(
                                'RuntimeException',
                                'outer',
                                "RuntimeException: outer\n",
                                '',
                                new Throwable(
                                    'LogicException',
                                    'inner',
                                    "LogicException: inner\n",
                                    '',
                                    null,
                                ),
                            ),
                        ),
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'successful test with display flags on but no issues' => [
                'successful_test_without_issues.txt',
                self::createTestResult(),
                true,
                true,
                true,
                true,
                true,
                true,
            ],

            'failed test with data provider' => [
                'failed_test_with_data_provider.txt',
                self::createTestResult(
                    testFailedEvents: [
                        new Failed(
                            self::telemetryInfo(),
                            self::testMethodWithDataProvider(),
                            ThrowableBuilder::from(
                                new ExpectationFailedException(
                                    'Failed asserting that 1 matches expected 2.',
                                ),
                            ),
                            null,
                        ),
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'risky phpt test' => [
                'risky_phpt_test.txt',
                self::createTestResult(
                    testConsideredRiskyEvents: [
                        'phpt-test' => [
                            new ConsideredRisky(
                                self::telemetryInfo(),
                                new Phpt('/path/to/test.phpt'),
                                'message',
                            ),
                        ],
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'skipped test without message' => [
                'skipped_test_without_message.txt',
                self::createTestResult(
                    testSkippedEvents: [
                        new TestSkipped(
                            self::telemetryInfo(),
                            self::testMethod(),
                            '',
                        ),
                    ],
                ),
                false,
                true,
                false,
                false,
                false,
                false,
            ],

            'PHPUnit test runner warning' => [
                'phpunit_test_runner_warning.txt',
                self::createTestResult(
                    testRunnerTriggeredWarningEvents: [
                        new TestRunnerWarningTriggered(
                            self::telemetryInfo(),
                            'message',
                        ),
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'PHPUnit test runner warnings are deduplicated' => [
                'phpunit_test_runner_warning.txt',
                self::createTestResult(
                    testRunnerTriggeredWarningEvents: [
                        new TestRunnerWarningTriggered(
                            self::telemetryInfo(),
                            'message',
                        ),
                        new TestRunnerWarningTriggered(
                            self::telemetryInfo(),
                            'message',
                        ),
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'PHPUnit test runner deprecation' => [
                'phpunit_test_runner_deprecation.txt',
                self::createTestResult(
                    testRunnerTriggeredDeprecationEvents: [
                        new TestRunnerDeprecationTriggered(
                            self::telemetryInfo(),
                            'message',
                        ),
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'PHPUnit test runner notice' => [
                'phpunit_test_runner_notice.txt',
                self::createTestResult(
                    testRunnerTriggeredNoticeEvents: [
                        new TestRunnerNoticeTriggered(
                            self::telemetryInfo(),
                            'message',
                        ),
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'PHPUnit test runner notices are deduplicated' => [
                'phpunit_test_runner_notice.txt',
                self::createTestResult(
                    testRunnerTriggeredNoticeEvents: [
                        new TestRunnerNoticeTriggered(
                            self::telemetryInfo(),
                            'message',
                        ),
                        new TestRunnerNoticeTriggered(
                            self::telemetryInfo(),
                            'message',
                        ),
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'test triggers PHPUnit error' => [
                'test_triggered_phpunit_error.txt',
                self::createTestResult(
                    testTriggeredPhpunitErrorEvents: [
                        'FooTest::testBar' => [
                            new PhpunitErrorTriggered(
                                self::telemetryInfo(),
                                self::testMethod(),
                                'message',
                            ),
                        ],
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'test triggers PHPUnit warning' => [
                'test_triggered_phpunit_warning.txt',
                self::createTestResult(
                    testTriggeredPhpunitWarningEvents: [
                        'FooTest::testBar' => [
                            new PhpunitWarningTriggered(
                                self::telemetryInfo(),
                                self::testMethod(),
                                'message',
                                false,
                            ),
                        ],
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'test triggers PHPUnit deprecation' => [
                'test_triggered_phpunit_deprecation.txt',
                self::createTestResult(
                    testTriggeredPhpunitDeprecationEvents: [
                        'FooTest::testBar' => [
                            new PhpunitDeprecationTriggered(
                                self::telemetryInfo(),
                                self::testMethod(),
                                'message',
                            ),
                        ],
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'test triggers PHPUnit notice' => [
                'test_triggered_phpunit_notice.txt',
                self::createTestResult(
                    testTriggeredPhpunitNoticeEvents: [
                        'FooTest::testBar' => [
                            new PhpunitNoticeTriggered(
                                self::telemetryInfo(),
                                self::testMethod(),
                                'message',
                            ),
                        ],
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                false,
            ],

            'error triggered outside of tests' => [
                'error_triggered_outside_of_tests.txt',
                self::createTestResult(
                    testRunnerTriggeredIssueErrorEvents: [
                        new TestRunnerIssueErrorTriggered(
                            self::telemetryInfo(),
                            'message',
                            'Foo.php',
                            1,
                            false,
                        ),
                    ],
                ),
                false,
                false,
                false,
                true,
                false,
                false,
            ],

            'issues triggered outside of tests are deduplicated' => [
                'duplicate_issues_triggered_outside_of_tests.txt',
                self::createTestResult(
                    testRunnerTriggeredIssueErrorEvents: [
                        new TestRunnerIssueErrorTriggered(
                            self::telemetryInfo(),
                            'message',
                            'Foo.php',
                            1,
                            false,
                        ),
                        new TestRunnerIssueErrorTriggered(
                            self::telemetryInfo(),
                            'message',
                            'Foo.php',
                            1,
                            false,
                        ),
                    ],
                ),
                false,
                false,
                false,
                true,
                false,
                false,
            ],

            'warning triggered outside of tests' => [
                'warning_triggered_outside_of_tests.txt',
                self::createTestResult(
                    testRunnerTriggeredIssueWarningEvents: [
                        new TestRunnerIssueWarningTriggered(
                            self::telemetryInfo(),
                            'message',
                            'Foo.php',
                            1,
                            false,
                            false,
                        ),
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                true,
            ],

            'PHP warning triggered outside of tests' => [
                'php_warning_triggered_outside_of_tests.txt',
                self::createTestResult(
                    testRunnerTriggeredIssuePhpWarningEvents: [
                        new TestRunnerIssuePhpWarningTriggered(
                            self::telemetryInfo(),
                            'message',
                            'Foo.php',
                            1,
                            false,
                            false,
                        ),
                    ],
                ),
                false,
                false,
                false,
                false,
                false,
                true,
            ],

            'notice triggered outside of tests' => [
                'notice_triggered_outside_of_tests.txt',
                self::createTestResult(
                    testRunnerTriggeredIssueNoticeEvents: [
                        new TestRunnerIssueNoticeTriggered(
                            self::telemetryInfo(),
                            'message',
                            'Foo.php',
                            1,
                            false,
                            false,
                        ),
                    ],
                ),
                false,
                false,
                false,
                false,
                true,
                false,
            ],

            'PHP notice triggered outside of tests' => [
                'php_notice_triggered_outside_of_tests.txt',
                self::createTestResult(
                    testRunnerTriggeredIssuePhpNoticeEvents: [
                        new TestRunnerIssuePhpNoticeTriggered(
                            self::telemetryInfo(),
                            'message',
                            'Foo.php',
                            1,
                            false,
                            false,
                        ),
                    ],
                ),
                false,
                false,
                false,
                false,
                true,
                false,
            ],

            'deprecation triggered outside of tests' => [
                'deprecation_triggered_outside_of_tests.txt',
                self::createTestResult(
                    testRunnerTriggeredIssueDeprecationEvents: [
                        new TestRunnerIssueDeprecationTriggered(
                            self::telemetryInfo(),
                            'message',
                            'Foo.php',
                            1,
                            false,
                            false,
                            false,
                            IssueTrigger::from(null, null),
                            'stack trace',
                        ),
                    ],
                ),
                false,
                false,
                true,
                false,
                false,
                false,
            ],

            'PHP deprecation triggered outside of tests' => [
                'php_deprecation_triggered_outside_of_tests.txt',
                self::createTestResult(
                    testRunnerTriggeredIssuePhpDeprecationEvents: [
                        new TestRunnerIssuePhpDeprecationTriggered(
                            self::telemetryInfo(),
                            'message',
                            'Foo.php',
                            1,
                            false,
                            false,
                            false,
                            IssueTrigger::from(null, null),
                        ),
                    ],
                ),
                false,
                false,
                true,
                false,
                false,
                false,
            ],
        ];
    }

    #[DataProvider('provider')]
    public function testPrintsExpectedOutputForTestResultObject(string $expectationFile, TestResult $result, bool $displayDetailsOnIncompleteTests, bool $displayDetailsOnSkippedTests, bool $displayDetailsOnTestsThatTriggerDeprecations, bool $displayDetailsOnTestsThatTriggerErrors, bool $displayDetailsOnTestsThatTriggerNotices, bool $displayDetailsOnTestsThatTriggerWarnings): void
    {
        $printer = $this->printer();

        $resultPrinter = new ResultPrinter(
            $printer,
            $displayDetailsOnIncompleteTests,
            $displayDetailsOnSkippedTests,
            $displayDetailsOnTestsThatTriggerDeprecations,
            $displayDetailsOnTestsThatTriggerErrors,
            $displayDetailsOnTestsThatTriggerNotices,
            $displayDetailsOnTestsThatTriggerWarnings,
        );

        $resultPrinter->print($result);

        /* @noinspection PhpPossiblePolymorphicInvocationInspection */
        $this->assertStringMatchesFormatFile(
            __DIR__ . '/expectations/result/' . $expectationFile,
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
     * @param list<Errored>                                   $testErroredEvents
     * @param list<Failed>                                    $testFailedEvents
     * @param array<string,list<ConsideredRisky>>             $testConsideredRiskyEvents
     * @param list<TestSkipped>                               $testSkippedEvents
     * @param list<MarkedIncomplete>                          $testMarkedIncompleteEvents
     * @param array<string,list<PhpunitDeprecationTriggered>> $testTriggeredPhpunitDeprecationEvents
     * @param array<string,list<PhpunitErrorTriggered>>       $testTriggeredPhpunitErrorEvents
     * @param array<string,list<PhpunitNoticeTriggered>>      $testTriggeredPhpunitNoticeEvents
     * @param array<string,list<PhpunitWarningTriggered>>     $testTriggeredPhpunitWarningEvents
     * @param list<TestRunnerDeprecationTriggered>            $testRunnerTriggeredDeprecationEvents
     * @param list<TestRunnerNoticeTriggered>                 $testRunnerTriggeredNoticeEvents
     * @param list<TestRunnerWarningTriggered>                $testRunnerTriggeredWarningEvents
     * @param list<TestRunnerIssueDeprecationTriggered>       $testRunnerTriggeredIssueDeprecationEvents
     * @param list<TestRunnerIssueErrorTriggered>             $testRunnerTriggeredIssueErrorEvents
     * @param list<TestRunnerIssueNoticeTriggered>            $testRunnerTriggeredIssueNoticeEvents
     * @param list<TestRunnerIssuePhpDeprecationTriggered>    $testRunnerTriggeredIssuePhpDeprecationEvents
     * @param list<TestRunnerIssuePhpNoticeTriggered>         $testRunnerTriggeredIssuePhpNoticeEvents
     * @param list<TestRunnerIssuePhpWarningTriggered>        $testRunnerTriggeredIssuePhpWarningEvents
     * @param list<TestRunnerIssueWarningTriggered>           $testRunnerTriggeredIssueWarningEvents
     * @param list<Issue>                                     $deprecations
     * @param list<Issue>                                     $phpDeprecations
     * @param list<Issue>                                     $errors
     * @param list<Issue>                                     $notices
     * @param list<Issue>                                     $phpNotices
     * @param list<Issue>                                     $warnings
     * @param list<Issue>                                     $phpWarnings
     */
    private static function createTestResult(int $numberOfTests = 1, int $numberOfTestsRun = 1, int $numberOfAssertions = 1, array $testErroredEvents = [], array $testFailedEvents = [], array $testConsideredRiskyEvents = [], array $testSkippedEvents = [], array $testMarkedIncompleteEvents = [], array $testTriggeredPhpunitDeprecationEvents = [], array $testTriggeredPhpunitErrorEvents = [], array $testTriggeredPhpunitNoticeEvents = [], array $testTriggeredPhpunitWarningEvents = [], array $testRunnerTriggeredDeprecationEvents = [], array $testRunnerTriggeredNoticeEvents = [], array $testRunnerTriggeredWarningEvents = [], array $testRunnerTriggeredIssueDeprecationEvents = [], array $testRunnerTriggeredIssueErrorEvents = [], array $testRunnerTriggeredIssueNoticeEvents = [], array $testRunnerTriggeredIssuePhpDeprecationEvents = [], array $testRunnerTriggeredIssuePhpNoticeEvents = [], array $testRunnerTriggeredIssuePhpWarningEvents = [], array $testRunnerTriggeredIssueWarningEvents = [], array $deprecations = [], array $phpDeprecations = [], array $errors = [], array $notices = [], array $phpNotices = [], array $warnings = [], array $phpWarnings = []): TestResult
    {
        return new TestResult(
            $numberOfTests,
            $numberOfTestsRun,
            $numberOfAssertions,
            $testErroredEvents,
            $testFailedEvents,
            $testConsideredRiskyEvents,
            [],                     // testSuiteSkippedEvents
            $testSkippedEvents,
            $testMarkedIncompleteEvents,
            $testTriggeredPhpunitDeprecationEvents,
            $testTriggeredPhpunitErrorEvents,
            $testTriggeredPhpunitNoticeEvents,
            $testTriggeredPhpunitWarningEvents,
            $testRunnerTriggeredDeprecationEvents,
            $testRunnerTriggeredNoticeEvents,
            $testRunnerTriggeredWarningEvents,
            $testRunnerTriggeredIssueDeprecationEvents,
            $testRunnerTriggeredIssueErrorEvents,
            $testRunnerTriggeredIssueNoticeEvents,
            $testRunnerTriggeredIssuePhpDeprecationEvents,
            $testRunnerTriggeredIssuePhpNoticeEvents,
            $testRunnerTriggeredIssuePhpWarningEvents,
            $testRunnerTriggeredIssueWarningEvents,
            $errors,
            $deprecations,
            $notices,
            $warnings,
            $phpDeprecations,
            $phpNotices,
            $phpWarnings,
            0,
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
