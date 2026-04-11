<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestRunner\TestResult;

use function array_map;
use function array_sum;
use function count;
use PHPUnit\Event\Test\AfterLastTestMethodErrored;
use PHPUnit\Event\Test\AfterLastTestMethodFailed;
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
use PHPUnit\Event\TestSuite\Skipped as TestSuiteSkipped;
use PHPUnit\TestRunner\TestResult\Issues\Issue;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class TestResult
{
    private int $numberOfTests;
    private int $numberOfTestsRun;
    private int $numberOfAssertions;

    /**
     * @var list<AfterLastTestMethodErrored|BeforeFirstTestMethodErrored|Errored>
     */
    private array $testErroredEvents;

    /**
     * @var list<AfterLastTestMethodFailed|BeforeFirstTestMethodFailed|Failed>
     */
    private array $testFailedEvents;

    /**
     * @var list<MarkedIncomplete>
     */
    private array $testMarkedIncompleteEvents;

    /**
     * @var list<TestSuiteSkipped>
     */
    private array $testSuiteSkippedEvents;

    /**
     * @var list<TestSkipped>
     */
    private array $testSkippedEvents;

    /**
     * @var array<string,list<ConsideredRisky>>
     */
    private array $testConsideredRiskyEvents;

    /**
     * @var array<string,list<PhpunitDeprecationTriggered>>
     */
    private array $testTriggeredPhpunitDeprecationEvents;

    /**
     * @var array<string,list<PhpunitErrorTriggered>>
     */
    private array $testTriggeredPhpunitErrorEvents;

    /**
     * @var array<string,list<PhpunitNoticeTriggered>>
     */
    private array $testTriggeredPhpunitNoticeEvents;

    /**
     * @var array<string,list<PhpunitWarningTriggered>>
     */
    private array $testTriggeredPhpunitWarningEvents;

    /**
     * @var list<TestRunnerDeprecationTriggered>
     */
    private array $testRunnerTriggeredDeprecationEvents;

    /**
     * @var list<TestRunnerNoticeTriggered>
     */
    private array $testRunnerTriggeredNoticeEvents;

    /**
     * @var list<TestRunnerWarningTriggered>
     */
    private array $testRunnerTriggeredWarningEvents;

    /**
     * @var list<TestRunnerIssueDeprecationTriggered>
     */
    private array $testRunnerTriggeredIssueDeprecationEvents;

    /**
     * @var list<TestRunnerIssueErrorTriggered>
     */
    private array $testRunnerTriggeredIssueErrorEvents;

    /**
     * @var list<TestRunnerIssueNoticeTriggered>
     */
    private array $testRunnerTriggeredIssueNoticeEvents;

    /**
     * @var list<TestRunnerIssuePhpDeprecationTriggered>
     */
    private array $testRunnerTriggeredIssuePhpDeprecationEvents;

    /**
     * @var list<TestRunnerIssuePhpNoticeTriggered>
     */
    private array $testRunnerTriggeredIssuePhpNoticeEvents;

    /**
     * @var list<TestRunnerIssuePhpWarningTriggered>
     */
    private array $testRunnerTriggeredIssuePhpWarningEvents;

    /**
     * @var list<TestRunnerIssueWarningTriggered>
     */
    private array $testRunnerTriggeredIssueWarningEvents;

    /**
     * @var list<Issue>
     */
    private array $errors;

    /**
     * @var list<Issue>
     */
    private array $deprecations;

    /**
     * @var list<Issue>
     */
    private array $notices;

    /**
     * @var list<Issue>
     */
    private array $warnings;

    /**
     * @var list<Issue>
     */
    private array $phpDeprecations;

    /**
     * @var list<Issue>
     */
    private array $phpNotices;

    /**
     * @var list<Issue>
     */
    private array $phpWarnings;

    /**
     * @var non-negative-int
     */
    private int $numberOfIssuesIgnoredByBaseline;

    /**
     * @param list<AfterLastTestMethodErrored|BeforeFirstTestMethodErrored|Errored> $testErroredEvents
     * @param list<AfterLastTestMethodFailed|BeforeFirstTestMethodFailed|Failed>    $testFailedEvents
     * @param array<string,list<ConsideredRisky>>                                   $testConsideredRiskyEvents
     * @param list<TestSuiteSkipped>                                                $testSuiteSkippedEvents
     * @param list<TestSkipped>                                                     $testSkippedEvents
     * @param list<MarkedIncomplete>                                                $testMarkedIncompleteEvents
     * @param array<string,list<PhpunitDeprecationTriggered>>                       $testTriggeredPhpunitDeprecationEvents
     * @param array<string,list<PhpunitErrorTriggered>>                             $testTriggeredPhpunitErrorEvents
     * @param array<string,list<PhpunitNoticeTriggered>>                            $testTriggeredPhpunitNoticeEvents
     * @param array<string,list<PhpunitWarningTriggered>>                           $testTriggeredPhpunitWarningEvents
     * @param list<TestRunnerDeprecationTriggered>                                  $testRunnerTriggeredDeprecationEvents
     * @param list<TestRunnerNoticeTriggered>                                       $testRunnerTriggeredNoticeEvents
     * @param list<TestRunnerWarningTriggered>                                      $testRunnerTriggeredWarningEvents
     * @param list<TestRunnerIssueDeprecationTriggered>                             $testRunnerTriggeredIssueDeprecationEvents
     * @param list<TestRunnerIssueErrorTriggered>                                   $testRunnerTriggeredIssueErrorEvents
     * @param list<TestRunnerIssueNoticeTriggered>                                  $testRunnerTriggeredIssueNoticeEvents
     * @param list<TestRunnerIssuePhpDeprecationTriggered>                          $testRunnerTriggeredIssuePhpDeprecationEvents
     * @param list<TestRunnerIssuePhpNoticeTriggered>                               $testRunnerTriggeredIssuePhpNoticeEvents
     * @param list<TestRunnerIssuePhpWarningTriggered>                              $testRunnerTriggeredIssuePhpWarningEvents
     * @param list<TestRunnerIssueWarningTriggered>                                 $testRunnerTriggeredIssueWarningEvents
     * @param list<Issue>                                                           $errors
     * @param list<Issue>                                                           $deprecations
     * @param list<Issue>                                                           $notices
     * @param list<Issue>                                                           $warnings
     * @param list<Issue>                                                           $phpDeprecations
     * @param list<Issue>                                                           $phpNotices
     * @param list<Issue>                                                           $phpWarnings
     * @param non-negative-int                                                      $numberOfIssuesIgnoredByBaseline
     */
    public function __construct(int $numberOfTests, int $numberOfTestsRun, int $numberOfAssertions, array $testErroredEvents, array $testFailedEvents, array $testConsideredRiskyEvents, array $testSuiteSkippedEvents, array $testSkippedEvents, array $testMarkedIncompleteEvents, array $testTriggeredPhpunitDeprecationEvents, array $testTriggeredPhpunitErrorEvents, array $testTriggeredPhpunitNoticeEvents, array $testTriggeredPhpunitWarningEvents, array $testRunnerTriggeredDeprecationEvents, array $testRunnerTriggeredNoticeEvents, array $testRunnerTriggeredWarningEvents, array $testRunnerTriggeredIssueDeprecationEvents, array $testRunnerTriggeredIssueErrorEvents, array $testRunnerTriggeredIssueNoticeEvents, array $testRunnerTriggeredIssuePhpDeprecationEvents, array $testRunnerTriggeredIssuePhpNoticeEvents, array $testRunnerTriggeredIssuePhpWarningEvents, array $testRunnerTriggeredIssueWarningEvents, array $errors, array $deprecations, array $notices, array $warnings, array $phpDeprecations, array $phpNotices, array $phpWarnings, int $numberOfIssuesIgnoredByBaseline)
    {
        $this->numberOfTests                                = $numberOfTests;
        $this->numberOfTestsRun                             = $numberOfTestsRun;
        $this->numberOfAssertions                           = $numberOfAssertions;
        $this->testErroredEvents                            = $testErroredEvents;
        $this->testFailedEvents                             = $testFailedEvents;
        $this->testConsideredRiskyEvents                    = $testConsideredRiskyEvents;
        $this->testSuiteSkippedEvents                       = $testSuiteSkippedEvents;
        $this->testSkippedEvents                            = $testSkippedEvents;
        $this->testMarkedIncompleteEvents                   = $testMarkedIncompleteEvents;
        $this->testTriggeredPhpunitDeprecationEvents        = $testTriggeredPhpunitDeprecationEvents;
        $this->testTriggeredPhpunitErrorEvents              = $testTriggeredPhpunitErrorEvents;
        $this->testTriggeredPhpunitNoticeEvents             = $testTriggeredPhpunitNoticeEvents;
        $this->testTriggeredPhpunitWarningEvents            = $testTriggeredPhpunitWarningEvents;
        $this->testRunnerTriggeredDeprecationEvents         = $testRunnerTriggeredDeprecationEvents;
        $this->testRunnerTriggeredNoticeEvents              = $testRunnerTriggeredNoticeEvents;
        $this->testRunnerTriggeredWarningEvents             = $testRunnerTriggeredWarningEvents;
        $this->testRunnerTriggeredIssueDeprecationEvents    = $testRunnerTriggeredIssueDeprecationEvents;
        $this->testRunnerTriggeredIssueErrorEvents          = $testRunnerTriggeredIssueErrorEvents;
        $this->testRunnerTriggeredIssueNoticeEvents         = $testRunnerTriggeredIssueNoticeEvents;
        $this->testRunnerTriggeredIssuePhpDeprecationEvents = $testRunnerTriggeredIssuePhpDeprecationEvents;
        $this->testRunnerTriggeredIssuePhpNoticeEvents      = $testRunnerTriggeredIssuePhpNoticeEvents;
        $this->testRunnerTriggeredIssuePhpWarningEvents     = $testRunnerTriggeredIssuePhpWarningEvents;
        $this->testRunnerTriggeredIssueWarningEvents        = $testRunnerTriggeredIssueWarningEvents;
        $this->errors                                       = $errors;
        $this->deprecations                                 = $deprecations;
        $this->notices                                      = $notices;
        $this->warnings                                     = $warnings;
        $this->phpDeprecations                              = $phpDeprecations;
        $this->phpNotices                                   = $phpNotices;
        $this->phpWarnings                                  = $phpWarnings;
        $this->numberOfIssuesIgnoredByBaseline              = $numberOfIssuesIgnoredByBaseline;
    }

    public function numberOfTestsRun(): int
    {
        return $this->numberOfTestsRun;
    }

    public function numberOfAssertions(): int
    {
        return $this->numberOfAssertions;
    }

    /**
     * @return list<AfterLastTestMethodErrored|BeforeFirstTestMethodErrored|Errored>
     */
    public function testErroredEvents(): array
    {
        return $this->testErroredEvents;
    }

    public function numberOfTestErroredEvents(): int
    {
        return count($this->testErroredEvents);
    }

    public function hasTestErroredEvents(): bool
    {
        return $this->numberOfTestErroredEvents() > 0;
    }

    /**
     * @return list<Failed>
     */
    public function testFailedEvents(): array
    {
        return $this->testFailedEvents;
    }

    public function numberOfTestFailedEvents(): int
    {
        return count($this->testFailedEvents);
    }

    public function hasTestFailedEvents(): bool
    {
        return $this->numberOfTestFailedEvents() > 0;
    }

    /**
     * @return array<string,list<ConsideredRisky>>
     */
    public function testConsideredRiskyEvents(): array
    {
        return $this->testConsideredRiskyEvents;
    }

    public function numberOfTestsWithTestConsideredRiskyEvents(): int
    {
        return count($this->testConsideredRiskyEvents);
    }

    public function hasTestConsideredRiskyEvents(): bool
    {
        return $this->numberOfTestsWithTestConsideredRiskyEvents() > 0;
    }

    /**
     * @return list<TestSuiteSkipped>
     */
    public function testSuiteSkippedEvents(): array
    {
        return $this->testSuiteSkippedEvents;
    }

    public function numberOfTestSkippedByTestSuiteSkippedEvents(): int
    {
        return array_sum(
            array_map(
                static fn (TestSuiteSkipped $event): int => $event->testSuite()->count(),
                $this->testSuiteSkippedEvents,
            ),
        );
    }

    public function hasTestSuiteSkippedEvents(): bool
    {
        return $this->numberOfTestSkippedByTestSuiteSkippedEvents() > 0;
    }

    /**
     * @return list<TestSkipped>
     */
    public function testSkippedEvents(): array
    {
        return $this->testSkippedEvents;
    }

    public function numberOfTestSkippedEvents(): int
    {
        return count($this->testSkippedEvents);
    }

    public function hasTestSkippedEvents(): bool
    {
        return $this->numberOfTestSkippedEvents() > 0;
    }

    /**
     * @return list<MarkedIncomplete>
     */
    public function testMarkedIncompleteEvents(): array
    {
        return $this->testMarkedIncompleteEvents;
    }

    public function numberOfTestMarkedIncompleteEvents(): int
    {
        return count($this->testMarkedIncompleteEvents);
    }

    public function hasTestMarkedIncompleteEvents(): bool
    {
        return $this->numberOfTestMarkedIncompleteEvents() > 0;
    }

    /**
     * @return array<string,list<PhpunitDeprecationTriggered>>
     */
    public function testTriggeredPhpunitDeprecationEvents(): array
    {
        return $this->testTriggeredPhpunitDeprecationEvents;
    }

    public function numberOfTestsWithTestTriggeredPhpunitDeprecationEvents(): int
    {
        return count($this->testTriggeredPhpunitDeprecationEvents);
    }

    public function hasTestTriggeredPhpunitDeprecationEvents(): bool
    {
        return $this->numberOfTestsWithTestTriggeredPhpunitDeprecationEvents() > 0;
    }

    /**
     * @return array<string,list<PhpunitErrorTriggered>>
     */
    public function testTriggeredPhpunitErrorEvents(): array
    {
        return $this->testTriggeredPhpunitErrorEvents;
    }

    public function numberOfTestsWithTestTriggeredPhpunitErrorEvents(): int
    {
        return count($this->testTriggeredPhpunitErrorEvents);
    }

    public function hasTestTriggeredPhpunitErrorEvents(): bool
    {
        return $this->numberOfTestsWithTestTriggeredPhpunitErrorEvents() > 0;
    }

    /**
     * @return array<string,list<PhpunitNoticeTriggered>>
     */
    public function testTriggeredPhpunitNoticeEvents(): array
    {
        return $this->testTriggeredPhpunitNoticeEvents;
    }

    public function numberOfTestsWithTestTriggeredPhpunitNoticeEvents(): int
    {
        return count($this->testTriggeredPhpunitNoticeEvents);
    }

    public function hasTestTriggeredPhpunitNoticeEvents(): bool
    {
        return $this->numberOfTestsWithTestTriggeredPhpunitNoticeEvents() > 0;
    }

    /**
     * @return array<string,list<PhpunitWarningTriggered>>
     */
    public function testTriggeredPhpunitWarningEvents(): array
    {
        return $this->testTriggeredPhpunitWarningEvents;
    }

    public function numberOfTestsWithTestTriggeredPhpunitWarningEvents(): int
    {
        return count($this->testTriggeredPhpunitWarningEvents);
    }

    public function hasTestTriggeredPhpunitWarningEvents(): bool
    {
        return $this->numberOfTestsWithTestTriggeredPhpunitWarningEvents() > 0;
    }

    /**
     * @return list<TestRunnerDeprecationTriggered>
     */
    public function testRunnerTriggeredDeprecationEvents(): array
    {
        return $this->testRunnerTriggeredDeprecationEvents;
    }

    public function numberOfTestRunnerTriggeredDeprecationEvents(): int
    {
        return count($this->testRunnerTriggeredDeprecationEvents);
    }

    public function hasTestRunnerTriggeredDeprecationEvents(): bool
    {
        return $this->numberOfTestRunnerTriggeredDeprecationEvents() > 0;
    }

    /**
     * @return list<TestRunnerNoticeTriggered>
     */
    public function testRunnerTriggeredNoticeEvents(): array
    {
        return $this->testRunnerTriggeredNoticeEvents;
    }

    public function numberOfTestRunnerTriggeredNoticeEvents(): int
    {
        return count($this->testRunnerTriggeredNoticeEvents);
    }

    public function hasTestRunnerTriggeredNoticeEvents(): bool
    {
        return $this->numberOfTestRunnerTriggeredNoticeEvents() > 0;
    }

    /**
     * @return list<TestRunnerWarningTriggered>
     */
    public function testRunnerTriggeredWarningEvents(): array
    {
        return $this->testRunnerTriggeredWarningEvents;
    }

    public function numberOfTestRunnerTriggeredWarningEvents(): int
    {
        return count($this->testRunnerTriggeredWarningEvents);
    }

    public function hasTestRunnerTriggeredWarningEvents(): bool
    {
        return $this->numberOfTestRunnerTriggeredWarningEvents() > 0;
    }

    /**
     * @return list<TestRunnerIssueDeprecationTriggered>
     */
    public function testRunnerTriggeredIssueDeprecationEvents(): array
    {
        return $this->testRunnerTriggeredIssueDeprecationEvents;
    }

    public function hasTestRunnerTriggeredIssueDeprecationEvents(): bool
    {
        return $this->testRunnerTriggeredIssueDeprecationEvents !== [];
    }

    /**
     * @return list<TestRunnerIssueErrorTriggered>
     */
    public function testRunnerTriggeredIssueErrorEvents(): array
    {
        return $this->testRunnerTriggeredIssueErrorEvents;
    }

    public function hasTestRunnerTriggeredIssueErrorEvents(): bool
    {
        return $this->testRunnerTriggeredIssueErrorEvents !== [];
    }

    /**
     * @return list<TestRunnerIssueNoticeTriggered>
     */
    public function testRunnerTriggeredIssueNoticeEvents(): array
    {
        return $this->testRunnerTriggeredIssueNoticeEvents;
    }

    public function hasTestRunnerTriggeredIssueNoticeEvents(): bool
    {
        return $this->testRunnerTriggeredIssueNoticeEvents !== [];
    }

    /**
     * @return list<TestRunnerIssuePhpDeprecationTriggered>
     */
    public function testRunnerTriggeredIssuePhpDeprecationEvents(): array
    {
        return $this->testRunnerTriggeredIssuePhpDeprecationEvents;
    }

    public function hasTestRunnerTriggeredIssuePhpDeprecationEvents(): bool
    {
        return $this->testRunnerTriggeredIssuePhpDeprecationEvents !== [];
    }

    /**
     * @return list<TestRunnerIssuePhpNoticeTriggered>
     */
    public function testRunnerTriggeredIssuePhpNoticeEvents(): array
    {
        return $this->testRunnerTriggeredIssuePhpNoticeEvents;
    }

    public function hasTestRunnerTriggeredIssuePhpNoticeEvents(): bool
    {
        return $this->testRunnerTriggeredIssuePhpNoticeEvents !== [];
    }

    /**
     * @return list<TestRunnerIssuePhpWarningTriggered>
     */
    public function testRunnerTriggeredIssuePhpWarningEvents(): array
    {
        return $this->testRunnerTriggeredIssuePhpWarningEvents;
    }

    public function hasTestRunnerTriggeredIssuePhpWarningEvents(): bool
    {
        return $this->testRunnerTriggeredIssuePhpWarningEvents !== [];
    }

    /**
     * @return list<TestRunnerIssueWarningTriggered>
     */
    public function testRunnerTriggeredIssueWarningEvents(): array
    {
        return $this->testRunnerTriggeredIssueWarningEvents;
    }

    public function hasTestRunnerTriggeredIssueWarningEvents(): bool
    {
        return $this->testRunnerTriggeredIssueWarningEvents !== [];
    }

    public function wasSuccessful(): bool
    {
        return !$this->hasTestErroredEvents() &&
               !$this->hasTestFailedEvents() &&
               !$this->hasTestTriggeredPhpunitErrorEvents();
    }

    public function hasIssues(): bool
    {
        return $this->hasTestsWithIssues() ||
               $this->hasTestRunnerTriggeredWarningEvents() ||
               $this->hasIssuesTriggeredOutsideOfTests();
    }

    public function hasIssuesTriggeredOutsideOfTests(): bool
    {
        return $this->hasTestRunnerTriggeredIssueDeprecationEvents() ||
               $this->hasTestRunnerTriggeredIssueErrorEvents() ||
               $this->hasTestRunnerTriggeredIssueNoticeEvents() ||
               $this->hasTestRunnerTriggeredIssuePhpDeprecationEvents() ||
               $this->hasTestRunnerTriggeredIssuePhpNoticeEvents() ||
               $this->hasTestRunnerTriggeredIssuePhpWarningEvents() ||
               $this->hasTestRunnerTriggeredIssueWarningEvents();
    }

    public function hasTestsWithIssues(): bool
    {
        return $this->hasRiskyTests() ||
               $this->hasIncompleteTests() ||
               $this->hasDeprecations() ||
               $this->errors !== [] ||
               $this->hasNotices() ||
               $this->hasWarnings() ||
               $this->hasPhpunitNotices() ||
               $this->hasPhpunitWarnings();
    }

    /**
     * @return list<Issue>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @return list<Issue>
     */
    public function deprecations(): array
    {
        return $this->deprecations;
    }

    /**
     * @return list<Issue>
     */
    public function notices(): array
    {
        return $this->notices;
    }

    /**
     * @return list<Issue>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }

    /**
     * @return list<Issue>
     */
    public function phpDeprecations(): array
    {
        return $this->phpDeprecations;
    }

    /**
     * @return list<Issue>
     */
    public function phpNotices(): array
    {
        return $this->phpNotices;
    }

    /**
     * @return list<Issue>
     */
    public function phpWarnings(): array
    {
        return $this->phpWarnings;
    }

    public function hasTests(): bool
    {
        return $this->numberOfTests > 0;
    }

    public function hasErrors(): bool
    {
        return $this->numberOfErrors() > 0;
    }

    public function numberOfErrors(): int
    {
        return $this->numberOfTestErroredEvents() +
               count($this->errors) +
               $this->numberOfTestsWithTestTriggeredPhpunitErrorEvents() +
               count($this->testRunnerTriggeredIssueErrorEvents);
    }

    public function hasDeprecations(): bool
    {
        return $this->numberOfDeprecations() > 0;
    }

    public function hasPhpOrUserDeprecations(): bool
    {
        return $this->numberOfPhpOrUserDeprecations() > 0;
    }

    public function numberOfPhpOrUserDeprecations(): int
    {
        return count($this->deprecations) +
               count($this->phpDeprecations) +
               count($this->testRunnerTriggeredIssueDeprecationEvents) +
               count($this->testRunnerTriggeredIssuePhpDeprecationEvents);
    }

    public function hasPhpunitDeprecations(): bool
    {
        return $this->numberOfPhpunitDeprecations() > 0;
    }

    public function numberOfPhpunitDeprecations(): int
    {
        return count($this->testTriggeredPhpunitDeprecationEvents) +
               count($this->testRunnerTriggeredDeprecationEvents);
    }

    public function hasPhpunitWarnings(): bool
    {
        return $this->numberOfPhpunitWarnings() > 0;
    }

    public function numberOfPhpunitWarnings(): int
    {
        return count($this->testTriggeredPhpunitWarningEvents) +
               count($this->testRunnerTriggeredWarningEvents);
    }

    public function numberOfDeprecations(): int
    {
        return count($this->deprecations) +
               count($this->phpDeprecations) +
               count($this->testTriggeredPhpunitDeprecationEvents) +
               count($this->testRunnerTriggeredDeprecationEvents) +
               count($this->testRunnerTriggeredIssueDeprecationEvents) +
               count($this->testRunnerTriggeredIssuePhpDeprecationEvents);
    }

    public function hasNotices(): bool
    {
        return $this->numberOfNotices() > 0;
    }

    public function numberOfNotices(): int
    {
        return count($this->notices) +
               count($this->phpNotices) +
               count($this->testRunnerTriggeredIssueNoticeEvents) +
               count($this->testRunnerTriggeredIssuePhpNoticeEvents);
    }

    public function hasWarnings(): bool
    {
        return $this->numberOfWarnings() > 0;
    }

    public function numberOfWarnings(): int
    {
        return count($this->warnings) +
               count($this->phpWarnings) +
               count($this->testRunnerTriggeredIssueWarningEvents) +
               count($this->testRunnerTriggeredIssuePhpWarningEvents);
    }

    public function hasIncompleteTests(): bool
    {
        return $this->testMarkedIncompleteEvents !== [];
    }

    public function hasRiskyTests(): bool
    {
        return $this->testConsideredRiskyEvents !== [];
    }

    public function hasSkippedTests(): bool
    {
        return $this->testSkippedEvents !== [];
    }

    public function hasIssuesIgnoredByBaseline(): bool
    {
        return $this->numberOfIssuesIgnoredByBaseline > 0;
    }

    /**
     * @return non-negative-int
     */
    public function numberOfIssuesIgnoredByBaseline(): int
    {
        return $this->numberOfIssuesIgnoredByBaseline;
    }

    public function hasPhpunitNotices(): bool
    {
        return $this->numberOfPhpunitNotices() > 0;
    }

    public function numberOfPhpunitNotices(): int
    {
        return $this->numberOfTestsWithTestTriggeredPhpunitNoticeEvents() +
               $this->numberOfTestRunnerTriggeredNoticeEvents();
    }
}
