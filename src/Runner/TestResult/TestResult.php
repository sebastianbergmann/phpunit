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
use PHPUnit\Event\Test\PassedWithWarning;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpErrorTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\WarningTriggered;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestResult
{
    private int $numberOfTests;
    private int $numberOfTestsRun;
    private int $numberOfAssertions;

    /**
     * @psalm-var list<BeforeFirstTestMethodErrored|Errored>
     */
    private array $testErroredEvents;

    /**
     * @psalm-var list<Failed>
     */
    private array $testFailedEvents;

    /**
     * @psalm-var list<PassedWithWarning>
     */
    private array $testPassedWithWarningEvents;

    /**
     * @psalm-var list<MarkedIncomplete>
     */
    private array $testMarkedIncompleteEvents;

    /**
     * @psalm-var list<Skipped>
     */
    private array $testSkippedEvents;

    /**
     * @psalm-var array<string,list<ConsideredRisky>>
     */
    private array $testConsideredRiskyEvents;

    /**
     * @psalm-var array<string,list<DeprecationTriggered>>
     */
    private array $testTriggeredDeprecationEvents = [];

    /**
     * @psalm-var array<string,list<PhpDeprecationTriggered>>
     */
    private array $testTriggeredPhpDeprecationEvents = [];

    /**
     * @psalm-var array<string,list<PhpunitDeprecationTriggered>>
     */
    private array $testTriggeredPhpunitDeprecationEvents = [];

    /**
     * @psalm-var array<string,list<ErrorTriggered>>
     */
    private array $testTriggeredErrorEvents = [];

    /**
     * @psalm-var array<string,list<PhpErrorTriggered>>
     */
    private array $testTriggeredPhpErrorEvents = [];

    /**
     * @psalm-var array<string,list<NoticeTriggered>>
     */
    private array $testTriggeredNoticeEvents = [];

    /**
     * @psalm-var array<string,list<PhpNoticeTriggered>>
     */
    private array $testTriggeredPhpNoticeEvents = [];

    /**
     * @psalm-var array<string,list<WarningTriggered>>
     */
    private array $testTriggeredWarningEvents = [];

    /**
     * @psalm-var array<string,list<PhpWarningTriggered>>
     */
    private array $testTriggeredPhpWarningEvents = [];

    /**
     * @psalm-var array<string,list<PhpunitWarningTriggered>>
     */
    private array $testTriggeredPhpunitWarningEvents = [];

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
     */
    public function __construct(int $numberOfTests, int $numberOfTestsRun, int $numberOfAssertions, array $testErroredEvents, array $testFailedEvents, array $testPassedWithWarningEvents, array $testConsideredRiskyEvents, array $testSkippedEvents, array $testMarkedIncompleteEvents, array $testTriggeredDeprecationEvents, array $testTriggeredPhpDeprecationEvents, array $testTriggeredPhpunitDeprecationEvents, array $testTriggeredErrorEvents, array $testTriggeredPhpErrorEvents, array $testTriggeredNoticeEvents, array $testTriggeredPhpNoticeEvents, array $testTriggeredWarningEvents, array $testTriggeredPhpWarningEvents, array $testTriggeredPhpunitWarningEvents)
    {
        $this->numberOfTests                         = $numberOfTests;
        $this->numberOfTestsRun                      = $numberOfTestsRun;
        $this->numberOfAssertions                    = $numberOfAssertions;
        $this->testErroredEvents                     = $testErroredEvents;
        $this->testFailedEvents                      = $testFailedEvents;
        $this->testPassedWithWarningEvents           = $testPassedWithWarningEvents;
        $this->testConsideredRiskyEvents             = $testConsideredRiskyEvents;
        $this->testSkippedEvents                     = $testSkippedEvents;
        $this->testMarkedIncompleteEvents            = $testMarkedIncompleteEvents;
        $this->testTriggeredDeprecationEvents        = $testTriggeredDeprecationEvents;
        $this->testTriggeredPhpDeprecationEvents     = $testTriggeredPhpDeprecationEvents;
        $this->testTriggeredPhpunitDeprecationEvents = $testTriggeredPhpunitDeprecationEvents;
        $this->testTriggeredErrorEvents              = $testTriggeredErrorEvents;
        $this->testTriggeredPhpErrorEvents           = $testTriggeredPhpErrorEvents;
        $this->testTriggeredNoticeEvents             = $testTriggeredNoticeEvents;
        $this->testTriggeredPhpNoticeEvents          = $testTriggeredPhpNoticeEvents;
        $this->testTriggeredWarningEvents            = $testTriggeredWarningEvents;
        $this->testTriggeredPhpWarningEvents         = $testTriggeredPhpWarningEvents;
        $this->testTriggeredPhpunitWarningEvents     = $testTriggeredPhpunitWarningEvents;
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
     * @psalm-return list<PassedWithWarning>
     */
    public function testPassedWithWarningEvents(): array
    {
        return $this->testPassedWithWarningEvents;
    }

    public function numberOfTestPassedWithWarningEvents(): int
    {
        return count($this->testPassedWithWarningEvents);
    }

    public function hasTestPassedWithWarningEvents(): bool
    {
        return $this->numberOfTestPassedWithWarningEvents() > 0;
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
     * @psalm-return list<Skipped>
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

    public function numberTestMarkedIncompleteEvents(): int
    {
        return count($this->testMarkedIncompleteEvents);
    }

    public function hasTestMarkedIncompleteEvents(): bool
    {
        return $this->numberTestMarkedIncompleteEvents() > 0;
    }

    public function wasSuccessful(): bool
    {
        return $this->wasSuccessfulIgnoringWarnings() && !$this->hasTestPassedWithWarningEvents();
    }

    public function wasSuccessfulIgnoringWarnings(): bool
    {
        return !$this->hasTestErroredEvents() && !$this->hasTestFailedEvents();
    }

    public function wasSuccessfulAndNoTestIsRiskyOrSkippedOrIncomplete(): bool
    {
        return $this->wasSuccessful() && !$this->hasTestConsideredRiskyEvents() && !$this->hasTestMarkedIncompleteEvents() && !$this->hasTestSkippedEvents();
    }
}
