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

use function count;
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
use PHPUnit\Event\Test\Skipped as TestSkipped;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\TestRunner\DeprecationTriggered as TestRunnerDeprecationTriggered;
use PHPUnit\Event\TestRunner\WarningTriggered as TestRunnerWarningTriggered;
use PHPUnit\Event\TestSuite\Skipped as TestSuiteSkipped;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestResult
{
    private readonly int $numberOfTests;
    private readonly int $numberOfTestsRun;
    private readonly int $numberOfAssertions;

    /**
     * @psalm-var list<BeforeFirstTestMethodErrored|Errored>
     */
    private readonly array $testErroredEvents;

    /**
     * @psalm-var list<Failed>
     */
    private readonly array $testFailedEvents;

    /**
     * @psalm-var list<MarkedIncomplete>
     */
    private readonly array $testMarkedIncompleteEvents;

    /**
     * @psalm-var list<TestSuiteSkipped>
     */
    private readonly array $testSuiteSkippedEvents;

    /**
     * @psalm-var list<TestSkipped>
     */
    private readonly array $testSkippedEvents;

    /**
     * @psalm-var array<string,list<ConsideredRisky>>
     */
    private readonly array $testConsideredRiskyEvents;

    /**
     * @psalm-var array<string,list<DeprecationTriggered>>
     */
    private readonly array $testTriggeredDeprecationEvents;

    /**
     * @psalm-var array<string,list<PhpDeprecationTriggered>>
     */
    private readonly array $testTriggeredPhpDeprecationEvents;

    /**
     * @psalm-var array<string,list<PhpunitDeprecationTriggered>>
     */
    private readonly array $testTriggeredPhpunitDeprecationEvents;

    /**
     * @psalm-var array<string,list<ErrorTriggered>>
     */
    private readonly array $testTriggeredErrorEvents;

    /**
     * @psalm-var array<string,list<NoticeTriggered>>
     */
    private readonly array $testTriggeredNoticeEvents;

    /**
     * @psalm-var array<string,list<PhpNoticeTriggered>>
     */
    private readonly array $testTriggeredPhpNoticeEvents;

    /**
     * @psalm-var array<string,list<WarningTriggered>>
     */
    private readonly array $testTriggeredWarningEvents;

    /**
     * @psalm-var array<string,list<PhpWarningTriggered>>
     */
    private readonly array $testTriggeredPhpWarningEvents;

    /**
     * @psalm-var array<string,list<PhpunitErrorTriggered>>
     */
    private readonly array $testTriggeredPhpunitErrorEvents;

    /**
     * @psalm-var array<string,list<PhpunitWarningTriggered>>
     */
    private readonly array $testTriggeredPhpunitWarningEvents;

    /**
     * @psalm-var list<TestRunnerDeprecationTriggered>
     */
    private readonly array $testRunnerTriggeredDeprecationEvents;

    /**
     * @psalm-var list<TestRunnerWarningTriggered>
     */
    private readonly array $testRunnerTriggeredWarningEvents;

    /**
     * @psalm-param list<BeforeFirstTestMethodErrored|Errored> $testErroredEvents
     * @psalm-param list<Failed> $testFailedEvents
     * @psalm-param array<string,list<ConsideredRisky>> $testConsideredRiskyEvents
     * @psalm-param list<TestSuiteSkipped> $testSuiteSkippedEvents
     * @psalm-param list<TestSkipped> $testSkippedEvents
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
    public function __construct(int $numberOfTests, int $numberOfTestsRun, int $numberOfAssertions, array $testErroredEvents, array $testFailedEvents, array $testConsideredRiskyEvents, array $testSuiteSkippedEvents, array $testSkippedEvents, array $testMarkedIncompleteEvents, array $testTriggeredDeprecationEvents, array $testTriggeredPhpDeprecationEvents, array $testTriggeredPhpunitDeprecationEvents, array $testTriggeredErrorEvents, array $testTriggeredNoticeEvents, array $testTriggeredPhpNoticeEvents, array $testTriggeredWarningEvents, array $testTriggeredPhpWarningEvents, array $testTriggeredPhpunitErrorEvents, array $testTriggeredPhpunitWarningEvents, array $testRunnerTriggeredDeprecationEvents, array $testRunnerTriggeredWarningEvents)
    {
        $this->numberOfTests                         = $numberOfTests;
        $this->numberOfTestsRun                      = $numberOfTestsRun;
        $this->numberOfAssertions                    = $numberOfAssertions;
        $this->testErroredEvents                     = $testErroredEvents;
        $this->testFailedEvents                      = $testFailedEvents;
        $this->testConsideredRiskyEvents             = $testConsideredRiskyEvents;
        $this->testSuiteSkippedEvents                = $testSuiteSkippedEvents;
        $this->testSkippedEvents                     = $testSkippedEvents;
        $this->testMarkedIncompleteEvents            = $testMarkedIncompleteEvents;
        $this->testTriggeredDeprecationEvents        = $testTriggeredDeprecationEvents;
        $this->testTriggeredPhpDeprecationEvents     = $testTriggeredPhpDeprecationEvents;
        $this->testTriggeredPhpunitDeprecationEvents = $testTriggeredPhpunitDeprecationEvents;
        $this->testTriggeredErrorEvents              = $testTriggeredErrorEvents;
        $this->testTriggeredNoticeEvents             = $testTriggeredNoticeEvents;
        $this->testTriggeredPhpNoticeEvents          = $testTriggeredPhpNoticeEvents;
        $this->testTriggeredWarningEvents            = $testTriggeredWarningEvents;
        $this->testTriggeredPhpWarningEvents         = $testTriggeredPhpWarningEvents;
        $this->testTriggeredPhpunitErrorEvents       = $testTriggeredPhpunitErrorEvents;
        $this->testTriggeredPhpunitWarningEvents     = $testTriggeredPhpunitWarningEvents;
        $this->testRunnerTriggeredDeprecationEvents  = $testRunnerTriggeredDeprecationEvents;
        $this->testRunnerTriggeredWarningEvents      = $testRunnerTriggeredWarningEvents;
    }

    public function numberOfTests(): int
    {
        return $this->numberOfTests;
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
     * @psalm-return list<BeforeFirstTestMethodErrored|Errored>
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
     * @psalm-return list<Failed>
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
     * @psalm-return array<string,list<ConsideredRisky>>
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
     * @psalm-return list<TestSuiteSkipped>
     */
    public function testSuiteSkippedEvents(): array
    {
        return $this->testSuiteSkippedEvents;
    }

    public function numberOfTestSuiteSkippedEvents(): int
    {
        return count($this->testSuiteSkippedEvents);
    }

    public function hasTestSuiteSkippedEvents(): bool
    {
        return $this->numberOfTestSuiteSkippedEvents() > 0;
    }

    /**
     * @psalm-return list<TestSkipped>
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
     * @psalm-return list<MarkedIncomplete>
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
     * @psalm-return array<string,list<DeprecationTriggered>>
     */
    public function testTriggeredDeprecationEvents(): array
    {
        return $this->testTriggeredDeprecationEvents;
    }

    public function numberOfTestsWithTestTriggeredDeprecationEvents(): int
    {
        return count($this->testTriggeredDeprecationEvents);
    }

    public function hasTestTriggeredDeprecationEvents(): bool
    {
        return $this->numberOfTestsWithTestTriggeredDeprecationEvents() > 0;
    }

    /**
     * @psalm-return array<string,list<PhpDeprecationTriggered>>
     */
    public function testTriggeredPhpDeprecationEvents(): array
    {
        return $this->testTriggeredPhpDeprecationEvents;
    }

    public function numberOfTestsWithTestTriggeredPhpDeprecationEvents(): int
    {
        return count($this->testTriggeredPhpDeprecationEvents);
    }

    public function hasTestTriggeredPhpDeprecationEvents(): bool
    {
        return $this->numberOfTestsWithTestTriggeredPhpDeprecationEvents() > 0;
    }

    /**
     * @psalm-return array<string,list<PhpunitDeprecationTriggered>>
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
     * @psalm-return array<string,list<ErrorTriggered>>
     */
    public function testTriggeredErrorEvents(): array
    {
        return $this->testTriggeredErrorEvents;
    }

    public function numberOfTestsWithTestTriggeredErrorEvents(): int
    {
        return count($this->testTriggeredErrorEvents);
    }

    public function hasTestTriggeredErrorEvents(): bool
    {
        return $this->numberOfTestsWithTestTriggeredErrorEvents() > 0;
    }

    /**
     * @psalm-return array<string,list<NoticeTriggered>>
     */
    public function testTriggeredNoticeEvents(): array
    {
        return $this->testTriggeredNoticeEvents;
    }

    public function numberOfTestsWithTestTriggeredNoticeEvents(): int
    {
        return count($this->testTriggeredNoticeEvents);
    }

    public function hasTestTriggeredNoticeEvents(): bool
    {
        return $this->numberOfTestsWithTestTriggeredNoticeEvents() > 0;
    }

    /**
     * @psalm-return array<string,list<PhpNoticeTriggered>>
     */
    public function testTriggeredPhpNoticeEvents(): array
    {
        return $this->testTriggeredPhpNoticeEvents;
    }

    public function numberOfTestsWithTestTriggeredPhpNoticeEvents(): int
    {
        return count($this->testTriggeredPhpNoticeEvents);
    }

    public function hasTestTriggeredPhpNoticeEvents(): bool
    {
        return $this->numberOfTestsWithTestTriggeredPhpNoticeEvents() > 0;
    }

    /**
     * @psalm-return array<string,list<WarningTriggered>>
     */
    public function testTriggeredWarningEvents(): array
    {
        return $this->testTriggeredWarningEvents;
    }

    public function numberOfTestsWithTestTriggeredWarningEvents(): int
    {
        return count($this->testTriggeredWarningEvents);
    }

    public function hasTestTriggeredWarningEvents(): bool
    {
        return $this->numberOfTestsWithTestTriggeredWarningEvents() > 0;
    }

    /**
     * @psalm-return array<string,list<PhpWarningTriggered>>
     */
    public function testTriggeredPhpWarningEvents(): array
    {
        return $this->testTriggeredPhpWarningEvents;
    }

    public function numberOfTestsWithTestTriggeredPhpWarningEvents(): int
    {
        return count($this->testTriggeredPhpWarningEvents);
    }

    public function hasTestTriggeredPhpWarningEvents(): bool
    {
        return $this->numberOfTestsWithTestTriggeredPhpWarningEvents() > 0;
    }

    /**
     * @psalm-return array<string,list<PhpunitErrorTriggered>>
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
     * @psalm-return array<string,list<PhpunitWarningTriggered>>
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
     * @psalm-return list<TestRunnerDeprecationTriggered>
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
     * @psalm-return list<TestRunnerWarningTriggered>
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

    public function hasDeprecationEvents(): bool
    {
        return $this->numberOfDeprecationEvents() > 0;
    }

    public function numberOfDeprecationEvents(): int
    {
        return $this->numberOfTestsWithTestTriggeredDeprecationEvents() +
               $this->numberOfTestsWithTestTriggeredPhpDeprecationEvents() +
               $this->numberOfTestRunnerTriggeredDeprecationEvents() +
               $this->numberOfTestsWithTestTriggeredPhpunitDeprecationEvents();
    }

    public function hasNoticeEvents(): bool
    {
        return $this->numberOfNoticeEvents() > 0;
    }

    public function numberOfNoticeEvents(): int
    {
        return $this->numberOfTestsWithTestTriggeredNoticeEvents() +
               $this->numberOfTestsWithTestTriggeredPhpNoticeEvents();
    }

    public function hasWarningEvents(): bool
    {
        return $this->numberOfWarningEvents() > 0;
    }

    public function numberOfWarningEvents(): int
    {
        return $this->numberOfTestsWithTestTriggeredWarningEvents() +
               $this->numberOfTestsWithTestTriggeredPhpWarningEvents() +
               $this->numberOfTestsWithTestTriggeredPhpunitWarningEvents() +
               $this->numberOfTestRunnerTriggeredWarningEvents();
    }

    public function wasSuccessful(): bool
    {
        return $this->wasSuccessfulIgnoringPhpunitWarnings() &&
               !$this->hasTestTriggeredPhpunitErrorEvents() &&
               !$this->hasTestRunnerTriggeredWarningEvents() &&
               !$this->hasTestTriggeredPhpunitWarningEvents();
    }

    public function wasSuccessfulIgnoringPhpunitWarnings(): bool
    {
        return !$this->hasTestErroredEvents() &&
               !$this->hasTestFailedEvents();
    }

    public function wasSuccessfulAndNoTestHasIssues(): bool
    {
        return $this->wasSuccessful() && !$this->hasTestsWithIssues();
    }

    public function hasTestsWithIssues(): bool
    {
        return $this->hasTestConsideredRiskyEvents() ||
               $this->hasTestMarkedIncompleteEvents() ||
               $this->hasDeprecationEvents() ||
               $this->hasTestTriggeredErrorEvents() ||
               $this->hasNoticeEvents() ||
               $this->hasWarningEvents();
    }
}
