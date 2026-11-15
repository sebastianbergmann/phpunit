<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Receipt;

use function array_fill;
use DateTimeImmutable;
use Exception;
use PHPUnit\Event\AbstractEventTestCase;
use PHPUnit\Event\Code\IssueTrigger\IssueTrigger;
use PHPUnit\Event\Code\TestCollection;
use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitErrorTriggered;
use PHPUnit\Event\Test\PhpunitNoticeTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\Skipped as TestSkipped;
use PHPUnit\Event\TestRunner\DeprecationTriggered as TestRunnerIssueDeprecationTriggered;
use PHPUnit\Event\TestRunner\ErrorTriggered as TestRunnerIssueErrorTriggered;
use PHPUnit\Event\TestRunner\NoticeTriggered as TestRunnerIssueNoticeTriggered;
use PHPUnit\Event\TestRunner\PhpDeprecationTriggered as TestRunnerIssuePhpDeprecationTriggered;
use PHPUnit\Event\TestRunner\PhpNoticeTriggered as TestRunnerIssuePhpNoticeTriggered;
use PHPUnit\Event\TestRunner\PhpunitDeprecationTriggered as TestRunnerDeprecationTriggered;
use PHPUnit\Event\TestRunner\PhpunitNoticeTriggered as TestRunnerNoticeTriggered;
use PHPUnit\Event\TestRunner\PhpunitWarningTriggered as TestRunnerWarningTriggered;
use PHPUnit\Event\TestRunner\PhpWarningTriggered as TestRunnerIssuePhpWarningTriggered;
use PHPUnit\Event\TestRunner\TimeLimitExceeded;
use PHPUnit\Event\TestRunner\WarningTriggered as TestRunnerIssueWarningTriggered;
use PHPUnit\Event\TestSuite\Skipped as TestSuiteSkipped;
use PHPUnit\Event\TestSuite\TestSuiteWithName;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\TestRunner\TestResult\Issues\Issue;
use PHPUnit\TestRunner\TestResult\TestResult;

#[CoversClass(OutcomeBuilder::class)]
#[UsesClass(Outcome::class)]
#[UsesClass(IssueCounts::class)]
#[UsesClass(TestResult::class)]
#[Small]
#[Group('test-runner')]
#[Group('test-runner/receipt')]
final class OutcomeBuilderTest extends AbstractEventTestCase
{
    /**
     * @return non-empty-array<non-empty-string, array{0: Status, 1: bool, 2: bool, 3: bool, 4: bool}>
     */
    public static function statusProvider(): array
    {
        return [
            'every selected test was run'                                 => [Status::Completed, true, false, false, false],
            'execution was aborted'                                       => [Status::Stopped, true, false, true, false],
            'a signal ended the test run'                                 => [Status::Interrupted, true, true, false, false],
            'a signal ended the test run and execution was aborted'       => [Status::Interrupted, true, true, true, false],
            'the test run was not started'                                => [Status::NotStarted, false, false, false, false],
            'the time limit was exceeded'                                 => [Status::TimeLimitExceeded, true, false, false, true],
            'the time limit was exceeded and execution was aborted'       => [Status::TimeLimitExceeded, true, false, true, true],
            'the time limit was exceeded and a signal ended the test run' => [Status::TimeLimitExceeded, true, true, true, true],
            'the time limit was exceeded before the test run was started' => [Status::TimeLimitExceeded, false, false, false, true],
        ];
    }

    #[DataProvider('statusProvider')]
    public function testKnowsHowTheTestRunEnded(Status $expected, bool $started, bool $interrupted, bool $executionAborted, bool $timeLimitExceeded): void
    {
        $outcome = (new OutcomeBuilder)->build(
            $this->emptyTestResult($executionAborted, $timeLimitExceeded),
            0,
            $started,
            $interrupted,
        );

        $this->assertSame($expected, $outcome->status());
    }

    public function testRecordsTheShellExitCode(): void
    {
        $outcome = (new OutcomeBuilder)->build($this->emptyTestResult(false, false), 124, true, false);

        $this->assertSame(124, $outcome->shellExitCode());
    }

    public function testRecordsTheTimeAtWhichItIsBuilt(): void
    {
        $before  = new DateTimeImmutable;
        $outcome = (new OutcomeBuilder)->build($this->emptyTestResult(false, false), 0, true, false);
        $after   = new DateTimeImmutable;

        $this->assertGreaterThanOrEqual($before, $outcome->endTime());
        $this->assertLessThanOrEqual($after, $outcome->endTime());
    }

    public function testCountsTests(): void
    {
        $outcome = (new OutcomeBuilder)->build($this->testResultWithDistinctNumbers(), 0, true, false);

        $this->assertSame(50, $outcome->numberOfTestsRun());
        $this->assertSame(60, $outcome->numberOfAssertions());
        $this->assertSame(2, $outcome->numberOfErroredTests());
        $this->assertSame(3, $outcome->numberOfFailedTests());
        $this->assertSame(11, $outcome->numberOfSkippedTests());
        $this->assertSame(7, $outcome->numberOfIncompleteTests());
        $this->assertSame(4, $outcome->numberOfRiskyTests());
    }

    public function testCountsIssuesTriggeredByTests(): void
    {
        $issues = (new OutcomeBuilder)->build($this->testResultWithDistinctNumbers(), 0, true, false)->issuesTriggeredByTests();

        $this->assertSame(8, $issues->errors());
        $this->assertSame(19, $issues->deprecations());
        $this->assertSame(23, $issues->notices());
        $this->assertSame(27, $issues->warnings());
        $this->assertSame(15, $issues->phpunitErrors());
        $this->assertSame(16, $issues->phpunitDeprecations());
        $this->assertSame(17, $issues->phpunitNotices());
        $this->assertSame(18, $issues->phpunitWarnings());
    }

    public function testCountsIssuesTriggeredByTestRunner(): void
    {
        $issues = (new OutcomeBuilder)->build($this->testResultWithDistinctNumbers(), 0, true, false)->issuesTriggeredByTestRunner();

        $this->assertSame(20, $issues->errors());
        $this->assertSame(43, $issues->deprecations());
        $this->assertSame(47, $issues->notices());
        $this->assertSame(51, $issues->warnings());
        $this->assertSame(0, $issues->phpunitErrors());
        $this->assertSame(30, $issues->phpunitDeprecations());
        $this->assertSame(31, $issues->phpunitNotices());
        $this->assertSame(32, $issues->phpunitWarnings());
    }

    private function emptyTestResult(bool $executionAborted, bool $timeLimitExceeded): TestResult
    {
        $timeLimitExceededEvent = null;

        if ($timeLimitExceeded) {
            $timeLimitExceededEvent = new TimeLimitExceeded($this->telemetryInfo(), 60);
        }

        return new TestResult(
            0,
            0,
            0,
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            [],
            0,
            [
                'self'     => 0,
                'direct'   => 0,
                'indirect' => 0,
                'unknown'  => 0,
            ],
            [],
            $timeLimitExceededEvent,
            [],
            $executionAborted,
        );
    }

    /**
     * Each number that the test result has is different from all others, so
     * that a number that is taken from the wrong place is noticed.
     */
    private function testResultWithDistinctNumbers(): TestResult
    {
        $info      = $this->telemetryInfo();
        $test      = $this->testValueObject();
        $throwable = ThrowableBuilder::from(new Exception('message'));
        $trigger   = IssueTrigger::from(null, null);
        $issue     = Issue::from('file.php', 1, 'description', $test);

        return new TestResult(
            100,
            50,
            60,
            array_fill(0, 2, new Errored($info, $test, $throwable)),
            array_fill(0, 3, new Failed($info, $test, $throwable, null)),
            $this->perTest(4, new ConsideredRisky($info, $test, 'message')),
            [new TestSuiteSkipped($info, new TestSuiteWithName('test suite', 5, TestCollection::fromArray([])), 'message')],
            array_fill(0, 6, new TestSkipped($info, $test, 'message')),
            array_fill(0, 7, new MarkedIncomplete($info, $test, $throwable)),
            $this->perTest(16, new PhpunitDeprecationTriggered($info, $test, 'message')),
            $this->perTest(15, new PhpunitErrorTriggered($info, $test, 'message')),
            $this->perTest(17, new PhpunitNoticeTriggered($info, $test, 'message')),
            $this->perTest(18, new PhpunitWarningTriggered($info, $test, 'message', false)),
            array_fill(0, 30, new TestRunnerDeprecationTriggered($info, 'message')),
            array_fill(0, 31, new TestRunnerNoticeTriggered($info, 'message')),
            array_fill(0, 32, new TestRunnerWarningTriggered($info, 'message')),
            array_fill(0, 21, new TestRunnerIssueDeprecationTriggered($info, 'message', 'file.php', 1, false, false, false, $trigger, 'stack trace')),
            array_fill(0, 20, new TestRunnerIssueErrorTriggered($info, 'message', 'file.php', 1, false)),
            array_fill(0, 23, new TestRunnerIssueNoticeTriggered($info, 'message', 'file.php', 1, false, false)),
            array_fill(0, 22, new TestRunnerIssuePhpDeprecationTriggered($info, 'message', 'file.php', 1, false, false, false, $trigger)),
            array_fill(0, 24, new TestRunnerIssuePhpNoticeTriggered($info, 'message', 'file.php', 1, false, false)),
            array_fill(0, 26, new TestRunnerIssuePhpWarningTriggered($info, 'message', 'file.php', 1, false, false)),
            array_fill(0, 25, new TestRunnerIssueWarningTriggered($info, 'message', 'file.php', 1, false, false)),
            array_fill(0, 8, $issue),
            array_fill(0, 9, $issue),
            array_fill(0, 11, $issue),
            array_fill(0, 13, $issue),
            array_fill(0, 10, $issue),
            array_fill(0, 12, $issue),
            array_fill(0, 14, $issue),
            0,
            [
                'self'     => 0,
                'direct'   => 0,
                'indirect' => 0,
                'unknown'  => 0,
            ],
        );
    }

    /**
     * @template T of object
     *
     * @param positive-int $numberOfTests
     * @param T            $event
     *
     * @return array<non-empty-string, list<T>>
     */
    private function perTest(int $numberOfTests, object $event): array
    {
        $events = [];

        for ($i = 1; $i <= $numberOfTests; $i++) {
            $events['Test::test' . $i] = [$event];
        }

        return $events;
    }
}
