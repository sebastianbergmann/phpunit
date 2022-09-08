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
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitErrorTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\TestRunner\DeprecationTriggered as TestRunnerDeprecationTriggered;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\TestRunner\WarningTriggered as TestRunnerWarningTriggered;
use PHPUnit\Event\TestSuite\Finished as TestSuiteFinished;
use PHPUnit\Event\TestSuite\Started as TestSuiteStarted;
use PHPUnit\Event\TestSuite\TestSuiteForTestClass;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Framework\TestSize\TestSize;
use PHPUnit\Metadata\Api\Groups;
use PHPUnit\Runner\NoIgnoredEventException;

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
     * @psalm-var array<string,list<PhpunitErrorTriggered>>
     */
    private array $testTriggeredPhpunitErrorEvents = [];

    /**
     * @psalm-var array<string,list<PhpunitWarningTriggered>>
     */
    private array $testTriggeredPhpunitWarningEvents = [];

    /**
     * @psalm-var list<TestRunnerWarningTriggered>
     */
    private array $testRunnerTriggeredWarningEvents = [];

    /**
     * @psalm-var list<TestRunnerDeprecationTriggered>
     */
    private array $testRunnerTriggeredDeprecationEvents                                                                                                            = [];
    private bool $ignoreTestTriggeredDeprecationEventForExpectation                                                                                                = false;
    private bool $ignoreTestTriggeredErrorEventForExpectation                                                                                                      = false;
    private bool $ignoreTestTriggeredNoticeEventForExpectation                                                                                                     = false;
    private bool $ignoreTestTriggeredWarningEventForExpectation                                                                                                    = false;
    private DeprecationTriggered|PhpDeprecationTriggered|ErrorTriggered|NoticeTriggered|PhpNoticeTriggered|WarningTriggered|PhpWarningTriggered|null $ignoredEvent = null;

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function __construct()
    {
        Facade::registerSubscribers(
            new ExecutionStartedSubscriber($this),
            new TestSuiteStartedSubscriber($this),
            new TestSuiteFinishedSubscriber($this),
            new TestPreparedSubscriber($this),
            new TestFinishedSubscriber($this),
            new BeforeTestClassMethodErroredSubscriber($this),
            new TestErroredSubscriber($this),
            new TestFailedSubscriber($this),
            new TestPassedSubscriber($this),
            new TestMarkedIncompleteSubscriber($this),
            new TestSkippedSubscriber($this),
            new TestConsideredRiskySubscriber($this),
            new TestTriggeredDeprecationSubscriber($this),
            new TestTriggeredErrorSubscriber($this),
            new TestTriggeredNoticeSubscriber($this),
            new TestTriggeredPhpDeprecationSubscriber($this),
            new TestTriggeredPhpNoticeSubscriber($this),
            new TestTriggeredPhpunitDeprecationSubscriber($this),
            new TestTriggeredPhpunitErrorSubscriber($this),
            new TestTriggeredPhpunitWarningSubscriber($this),
            new TestTriggeredPhpWarningSubscriber($this),
            new TestTriggeredWarningSubscriber($this),
            new TestRunnerTriggeredDeprecationSubscriber($this),
            new TestRunnerTriggeredWarningSubscriber($this),
        );
    }

    public function result(): TestResult
    {
        return new TestResult(
            $this->numberOfTests,
            $this->numberOfTestsRun,
            $this->numberOfAssertions,
            $this->testErroredEvents,
            $this->testFailedEvents,
            $this->testConsideredRiskyEvents,
            $this->testSkippedEvents,
            $this->testMarkedIncompleteEvents,
            $this->testTriggeredDeprecationEvents,
            $this->testTriggeredPhpDeprecationEvents,
            $this->testTriggeredPhpunitDeprecationEvents,
            $this->testTriggeredErrorEvents,
            $this->testTriggeredNoticeEvents,
            $this->testTriggeredPhpNoticeEvents,
            $this->testTriggeredWarningEvents,
            $this->testTriggeredPhpWarningEvents,
            $this->testTriggeredPhpunitErrorEvents,
            $this->testTriggeredPhpunitWarningEvents,
            $this->testRunnerTriggeredDeprecationEvents,
            $this->testRunnerTriggeredWarningEvents,
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

    public function hasTestRunnerTriggeredWarningEvents(): bool
    {
        return !empty($this->testRunnerTriggeredWarningEvents);
    }

    /**
     * @psalm-return list<TestRunnerWarningTriggered>
     */
    public function testRunnerTriggeredWarningEvents(): array
    {
        return $this->testRunnerTriggeredWarningEvents;
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

        $this->prepared                                          = false;
        $this->ignoreTestTriggeredDeprecationEventForExpectation = false;
        $this->ignoreTestTriggeredErrorEventForExpectation       = false;
        $this->ignoreTestTriggeredNoticeEventForExpectation      = false;
        $this->ignoreTestTriggeredWarningEventForExpectation     = false;
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
        if ($this->ignoreTestTriggeredDeprecationEventForExpectation) {
            $this->ignoredEvent = $event;

            return;
        }

        if (!isset($this->testTriggeredDeprecationEvents[$event->test()->id()])) {
            $this->testTriggeredDeprecationEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredDeprecationEvents[$event->test()->id()][] = $event;
    }

    public function testTriggeredPhpDeprecation(PhpDeprecationTriggered $event): void
    {
        if ($this->ignoreTestTriggeredDeprecationEventForExpectation) {
            $this->ignoredEvent = $event;

            return;
        }

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
        if ($this->ignoreTestTriggeredErrorEventForExpectation) {
            $this->ignoredEvent = $event;

            return;
        }

        if (!isset($this->testTriggeredErrorEvents[$event->test()->id()])) {
            $this->testTriggeredErrorEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredErrorEvents[$event->test()->id()][] = $event;
    }

    public function testTriggeredNotice(NoticeTriggered $event): void
    {
        if ($this->ignoreTestTriggeredNoticeEventForExpectation) {
            $this->ignoredEvent = $event;

            return;
        }

        if (!isset($this->testTriggeredNoticeEvents[$event->test()->id()])) {
            $this->testTriggeredNoticeEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredNoticeEvents[$event->test()->id()][] = $event;
    }

    public function testTriggeredPhpNotice(PhpNoticeTriggered $event): void
    {
        if ($this->ignoreTestTriggeredNoticeEventForExpectation) {
            $this->ignoredEvent = $event;

            return;
        }

        if (!isset($this->testTriggeredPhpNoticeEvents[$event->test()->id()])) {
            $this->testTriggeredPhpNoticeEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredPhpNoticeEvents[$event->test()->id()][] = $event;
    }

    public function testTriggeredWarning(WarningTriggered $event): void
    {
        if ($this->ignoreTestTriggeredWarningEventForExpectation) {
            $this->ignoredEvent = $event;

            return;
        }

        if (!isset($this->testTriggeredWarningEvents[$event->test()->id()])) {
            $this->testTriggeredWarningEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredWarningEvents[$event->test()->id()][] = $event;
    }

    public function testTriggeredPhpWarning(PhpWarningTriggered $event): void
    {
        if ($this->ignoreTestTriggeredWarningEventForExpectation) {
            $this->ignoredEvent = $event;

            return;
        }

        if (!isset($this->testTriggeredPhpWarningEvents[$event->test()->id()])) {
            $this->testTriggeredPhpWarningEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredPhpWarningEvents[$event->test()->id()][] = $event;
    }

    public function testTriggeredPhpunitError(PhpunitErrorTriggered $event): void
    {
        if (!isset($this->testTriggeredPhpunitErrorEvents[$event->test()->id()])) {
            $this->testTriggeredPhpunitErrorEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredPhpunitErrorEvents[$event->test()->id()][] = $event;
    }

    public function testTriggeredPhpunitWarning(PhpunitWarningTriggered $event): void
    {
        if (!isset($this->testTriggeredPhpunitWarningEvents[$event->test()->id()])) {
            $this->testTriggeredPhpunitWarningEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredPhpunitWarningEvents[$event->test()->id()][] = $event;
    }

    public function testRunnerTriggeredDeprecation(TestRunnerDeprecationTriggered $event): void
    {
        $this->testRunnerTriggeredDeprecationEvents[] = $event;
    }

    public function testRunnerTriggeredWarning(TestRunnerWarningTriggered $event): void
    {
        $this->testRunnerTriggeredWarningEvents[] = $event;
    }

    public function hasWarningEvents(): bool
    {
        return !empty($this->testTriggeredWarningEvents) ||
               !empty($this->testTriggeredPhpWarningEvents) ||
               !empty($this->testTriggeredPhpunitWarningEvents) ||
               !empty($this->testRunnerTriggeredWarningEvents);
    }

    public function ignoreTestTriggeredDeprecationEventForExpectation(): void
    {
        $this->ignoreTestTriggeredDeprecationEventForExpectation = true;
    }

    public function ignoreTestTriggeredErrorEventForExpectation(): void
    {
        $this->ignoreTestTriggeredErrorEventForExpectation = true;
    }

    public function ignoreTestTriggeredNoticeEventForExpectation(): void
    {
        $this->ignoreTestTriggeredNoticeEventForExpectation = true;
    }

    public function ignoreTestTriggeredWarningEventForExpectation(): void
    {
        $this->ignoreTestTriggeredWarningEventForExpectation = true;
    }

    public function hasIgnoredEvent(): bool
    {
        return $this->ignoredEvent !== null;
    }

    /**
     * @throws NoIgnoredEventException
     */
    public function ignoredEvent(): DeprecationTriggered|PhpDeprecationTriggered|ErrorTriggered|NoticeTriggered|PhpNoticeTriggered|WarningTriggered|PhpWarningTriggered
    {
        if ($this->ignoredEvent === null) {
            throw new NoIgnoredEventException;
        }

        return $this->ignoredEvent;
    }
}
