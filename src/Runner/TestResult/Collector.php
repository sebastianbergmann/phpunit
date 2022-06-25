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

use function assert;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErrorTriggered;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\NoticeTriggered;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\PassedWithWarning;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpErrorTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\TestSuite\Finished as TestSuiteFinished;
use PHPUnit\Event\TestSuite\Started as TestSuiteStarted;
use PHPUnit\Event\TestSuite\TestSuiteForTestClass;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Framework\TestSize\TestSize;
use PHPUnit\Metadata\Api\Groups;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Collector
{
    private int $numberOfTests                       = 0;
    private int $numberOfTestsRun                    = 0;
    private int $numberOfAssertions                  = 0;
    private bool $prepared                           = false;
    private bool $currentTestSuiteForTestClassFailed = false;

    /**
     * @psalm-var list<class-string>
     */
    private array $passedTestClasses = [];

    /**
     * @psalm-var array<string,array{result: mixed, size: TestSize}>
     */
    private array $passedTestMethods = [];

    /**
     * @psalm-var list<BeforeFirstTestMethodErrored|Errored>
     */
    private array $testErroredEvents = [];

    /**
     * @psalm-var list<Failed>
     */
    private array $testFailedEvents = [];

    /**
     * @psalm-var list<PassedWithWarning>
     */
    private array $testPassedWithWarningEvents = [];

    /**
     * @psalm-var list<MarkedIncomplete>
     */
    private array $testMarkedIncompleteEvents = [];

    /**
     * @psalm-var list<Skipped>
     */
    private array $testSkippedEvents = [];

    /**
     * @psalm-var array<string,list<ConsideredRisky>>
     */
    private array $testConsideredRiskyEvents = [];

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
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function __construct()
    {
        Facade::registerSubscriber(new ExecutionStartedSubscriber($this));
        Facade::registerSubscriber(new TestSuiteStartedSubscriber($this));
        Facade::registerSubscriber(new TestSuiteFinishedSubscriber($this));
        Facade::registerSubscriber(new TestPreparedSubscriber($this));
        Facade::registerSubscriber(new TestFinishedSubscriber($this));
        Facade::registerSubscriber(new BeforeTestClassMethodErroredSubscriber($this));
        Facade::registerSubscriber(new TestErroredSubscriber($this));
        Facade::registerSubscriber(new TestFailedSubscriber($this));
        Facade::registerSubscriber(new TestPassedSubscriber($this));
        Facade::registerSubscriber(new TestPassedWithWarningSubscriber($this));
        Facade::registerSubscriber(new TestMarkedIncompleteSubscriber($this));
        Facade::registerSubscriber(new TestSkippedSubscriber($this));
        Facade::registerSubscriber(new TestConsideredRiskySubscriber($this));
        Facade::registerSubscriber(new TestTriggeredDeprecationSubscriber($this));
        Facade::registerSubscriber(new TestTriggeredErrorSubscriber($this));
        Facade::registerSubscriber(new TestTriggeredNoticeSubscriber($this));
        Facade::registerSubscriber(new TestTriggeredPhpDeprecationSubscriber($this));
        Facade::registerSubscriber(new TestTriggeredPhpErrorSubscriber($this));
        Facade::registerSubscriber(new TestTriggeredPhpNoticeSubscriber($this));
        Facade::registerSubscriber(new TestTriggeredPhpunitDeprecationSubscriber($this));
        Facade::registerSubscriber(new TestTriggeredPhpunitWarningSubscriber($this));
        Facade::registerSubscriber(new TestTriggeredPhpWarningSubscriber($this));
        Facade::registerSubscriber(new TestTriggeredWarningSubscriber($this));
    }

    public function result(): TestResult
    {
        return new TestResult(
            $this->numberOfTests,
            $this->numberOfTestsRun,
            $this->numberOfAssertions,
            $this->testErroredEvents,
            $this->testFailedEvents,
            $this->testPassedWithWarningEvents,
            $this->testConsideredRiskyEvents,
            $this->testSkippedEvents,
            $this->testMarkedIncompleteEvents,
            $this->testTriggeredDeprecationEvents,
            $this->testTriggeredPhpDeprecationEvents,
            $this->testTriggeredPhpunitDeprecationEvents,
            $this->testTriggeredErrorEvents,
            $this->testTriggeredPhpErrorEvents,
            $this->testTriggeredNoticeEvents,
            $this->testTriggeredPhpNoticeEvents,
            $this->testTriggeredWarningEvents,
            $this->testTriggeredPhpWarningEvents,
            $this->testTriggeredPhpunitWarningEvents
        );
    }

    public function hasTestErroredEvents(): bool
    {
        return !empty($this->testErroredEvents);
    }

    public function hasTestFailedEvents(): bool
    {
        return !empty($this->testFailedEvents);
    }

    public function hasTestPassedWithWarningEvents(): bool
    {
        return !empty($this->testPassedWithWarningEvents);
    }

    public function hasTestConsideredRiskyEvents(): bool
    {
        return !empty($this->testConsideredRiskyEvents);
    }

    public function hasTestSkippedEvents(): bool
    {
        return !empty($this->testSkippedEvents);
    }

    public function hasTestMarkedIncompleteEvents(): bool
    {
        return !empty($this->testMarkedIncompleteEvents);
    }

    /**
     * @psalm-return list<class-string>
     */
    public function passedTestClasses(): array
    {
        return $this->passedTestClasses;
    }

    /**
     * @psalm-return array<string,array{result: mixed, size: TestSize}>
     */
    public function passedTestMethods(): array
    {
        return $this->passedTestMethods;
    }

    public function executionStarted(ExecutionStarted $event): void
    {
        $this->numberOfTests = $event->testSuite()->count();
    }

    public function testSuiteStarted(TestSuiteStarted $event): void
    {
        $testSuite = $event->testSuite();

        if (!$testSuite->isForTestClass()) {
            return;
        }

        $this->currentTestSuiteForTestClassFailed = false;
    }

    public function testSuiteFinished(TestSuiteFinished $event): void
    {
        $testSuite = $event->testSuite();

        if (!$testSuite->isForTestClass()) {
            return;
        }

        assert($testSuite instanceof TestSuiteForTestClass);

        if (!$this->currentTestSuiteForTestClassFailed) {
            $this->passedTestClasses[] = $testSuite->className();
        }
    }

    public function testPrepared(): void
    {
        $this->prepared = true;
    }

    public function testFinished(Finished $event): void
    {
        $this->numberOfAssertions += $event->numberOfAssertionsPerformed();

        $this->numberOfTestsRun++;

        $this->prepared = false;
    }

    public function beforeTestClassMethodErrored(BeforeFirstTestMethodErrored $event): void
    {
        $this->testErroredEvents[] = $event;

        $this->numberOfTestsRun++;
    }

    public function testErrored(Errored $event): void
    {
        $this->testErroredEvents[] = $event;

        $this->currentTestSuiteForTestClassFailed = true;

        /*
         * @todo Eliminate this special case
         */
        if (str_contains($event->asString(), 'Test was run in child process and ended unexpectedly')) {
            return;
        }

        if (!$this->prepared) {
            $this->numberOfTestsRun++;
        }
    }

    public function testFailed(Failed $event): void
    {
        $this->testFailedEvents[] = $event;

        $this->currentTestSuiteForTestClassFailed = true;
    }

    public function testPassed(Passed $event): void
    {
        if (!$event->test()->isTestMethod()) {
            return;
        }

        $test = $event->test();

        assert($test instanceof TestMethod);

        $size = (new Groups)->size(
            $test->className(),
            $test->methodName()
        );

        $this->passedTestMethods[$test->nameWithClass()] = [
            'result' => $event->testMethodReturnValue(),
            'size'   => $size,
        ];
    }

    public function testPassedWithWarning(PassedWithWarning $event): void
    {
        $this->testPassedWithWarningEvents[] = $event;
    }

    public function testMarkedIncomplete(MarkedIncomplete $event): void
    {
        $this->testMarkedIncompleteEvents[] = $event;
    }

    public function testSkipped(Skipped $event): void
    {
        $this->testSkippedEvents[] = $event;

        if (!$this->prepared) {
            $this->numberOfTestsRun++;
        }
    }

    public function testConsideredRisky(ConsideredRisky $event): void
    {
        if (!isset($this->testConsideredRiskyEvents[$event->test()->id()])) {
            $this->testConsideredRiskyEvents[$event->test()->id()] = [];
        }

        $this->testConsideredRiskyEvents[$event->test()->id()][] = $event;
    }

    public function testTriggeredDeprecation(DeprecationTriggered $event): void
    {
        if (!isset($this->testTriggeredDeprecationEvents[$event->test()->id()])) {
            $this->testTriggeredDeprecationEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredDeprecationEvents[$event->test()->id()][] = $event;
    }

    public function testTriggeredPhpDeprecation(PhpDeprecationTriggered $event): void
    {
        if (!isset($this->testTriggeredPhpDeprecationEvents[$event->test()->id()])) {
            $this->testTriggeredPhpDeprecationEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredPhpDeprecationEvents[$event->test()->id()][] = $event;
    }

    public function testTriggeredPhpunitDeprecation(PhpunitDeprecationTriggered $event): void
    {
        if (!isset($this->testTriggeredPhpunitDeprecationEvents[$event->test()->id()])) {
            $this->testTriggeredPhpunitDeprecationEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredPhpunitDeprecationEvents[$event->test()->id()][] = $event;
    }

    public function testTriggeredError(ErrorTriggered $event): void
    {
        if (!isset($this->testTriggeredErrorEvents[$event->test()->id()])) {
            $this->testTriggeredErrorEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredErrorEvents[$event->test()->id()][] = $event;
    }

    public function testTriggeredPhpError(PhpErrorTriggered $event): void
    {
        if (!isset($this->testTriggeredPhpErrorEvents[$event->test()->id()])) {
            $this->testTriggeredPhpErrorEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredPhpErrorEvents[$event->test()->id()][] = $event;
    }

    public function testTriggeredNotice(NoticeTriggered $event): void
    {
        if (!isset($this->testTriggeredNoticeEvents[$event->test()->id()])) {
            $this->testTriggeredNoticeEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredNoticeEvents[$event->test()->id()][] = $event;
    }

    public function testTriggeredPhpNotice(PhpNoticeTriggered $event): void
    {
        if (!isset($this->testTriggeredPhpNoticeEvents[$event->test()->id()])) {
            $this->testTriggeredPhpNoticeEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredPhpNoticeEvents[$event->test()->id()][] = $event;
    }

    public function testTriggeredWarning(WarningTriggered $event): void
    {
        if (!isset($this->testTriggeredWarningEvents[$event->test()->id()])) {
            $this->testTriggeredWarningEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredWarningEvents[$event->test()->id()][] = $event;
    }

    public function testTriggeredPhpWarning(PhpWarningTriggered $event): void
    {
        if (!isset($this->testTriggeredPhpWarningEvents[$event->test()->id()])) {
            $this->testTriggeredPhpWarningEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredPhpWarningEvents[$event->test()->id()][] = $event;
    }

    public function testTriggeredPhpunitWarning(PhpunitWarningTriggered $event): void
    {
        if (!isset($this->testTriggeredPhpunitWarningEvents[$event->test()->id()])) {
            $this->testTriggeredPhpunitWarningEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredPhpunitWarningEvents[$event->test()->id()][] = $event;
    }
}
