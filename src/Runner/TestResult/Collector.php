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

use function array_merge;
use function array_values;
use function assert;
use function count;
use function implode;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Test\AfterLastTestMethodErrored;
use PHPUnit\Event\Test\AfterLastTestMethodFailed;
use PHPUnit\Event\Test\AttemptErrored;
use PHPUnit\Event\Test\AttemptFailed;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\BeforeFirstTestMethodFailed;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErrorTriggered;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\NoticeTriggered;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitErrorTriggered;
use PHPUnit\Event\Test\PhpunitNoticeTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\Skipped as TestSkipped;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\TestRunner\ChildProcessErrored;
use PHPUnit\Event\TestRunner\DeprecationTriggered as TestRunnerDeprecationTriggered;
use PHPUnit\Event\TestRunner\ErrorTriggered as TestRunnerIssueErrorTriggered;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\TestRunner\Issue\DeprecationTriggered as TestRunnerIssueDeprecationTriggered;
use PHPUnit\Event\TestRunner\Issue\NoticeTriggered as TestRunnerIssueNoticeTriggered;
use PHPUnit\Event\TestRunner\Issue\WarningTriggered as TestRunnerIssueWarningTriggered;
use PHPUnit\Event\TestRunner\NoticeTriggered as TestRunnerNoticeTriggered;
use PHPUnit\Event\TestRunner\PhpDeprecationTriggered as TestRunnerIssuePhpDeprecationTriggered;
use PHPUnit\Event\TestRunner\PhpNoticeTriggered as TestRunnerIssuePhpNoticeTriggered;
use PHPUnit\Event\TestRunner\PhpWarningTriggered as TestRunnerIssuePhpWarningTriggered;
use PHPUnit\Event\TestRunner\WarningTriggered as TestRunnerWarningTriggered;
use PHPUnit\Event\TestSuite\Finished as TestSuiteFinished;
use PHPUnit\Event\TestSuite\Skipped as TestSuiteSkipped;
use PHPUnit\Event\TestSuite\Started as TestSuiteStarted;
use PHPUnit\Event\TestSuite\TestSuiteForRepeatedTestMethod;
use PHPUnit\Event\TestSuite\TestSuiteForTestClass;
use PHPUnit\Event\TestSuite\TestSuiteForTestMethodWithDataProvider;
use PHPUnit\TestRunner\IssueFilter;
use PHPUnit\TestRunner\TestResult\Issues\Issue;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Collector
{
    private readonly IssueFilter $issueFilter;
    private int $numberOfTests        = 0;
    private int $numberOfTestsRun     = 0;
    private int $numberOfAssertions   = 0;
    private bool $prepared            = false;
    private bool $childProcessErrored = false;

    /**
     * @var non-negative-int
     */
    private int $numberOfIssuesIgnoredByBaseline = 0;

    /**
     * @var list<AfterLastTestMethodErrored|BeforeFirstTestMethodErrored|Errored>
     */
    private array $testErroredEvents = [];

    /**
     * @var list<AfterLastTestMethodFailed|BeforeFirstTestMethodFailed|Failed>
     */
    private array $testFailedEvents = [];

    /**
     * @var list<MarkedIncomplete>
     */
    private array $testMarkedIncompleteEvents = [];

    /**
     * @var list<TestSuiteSkipped>
     */
    private array $testSuiteSkippedEvents = [];

    /**
     * @var list<TestSkipped>
     */
    private array $testSkippedEvents = [];

    /**
     * @var array<string,list<ConsideredRisky>>
     */
    private array $testConsideredRiskyEvents = [];

    /**
     * @var array<string,list<PhpunitDeprecationTriggered>>
     */
    private array $testTriggeredPhpunitDeprecationEvents = [];

    /**
     * @var array<string,list<PhpunitErrorTriggered>>
     */
    private array $testTriggeredPhpunitErrorEvents = [];

    /**
     * @var array<string,list<PhpunitNoticeTriggered>>
     */
    private array $testTriggeredPhpunitNoticeEvents = [];

    /**
     * @var array<string,list<PhpunitWarningTriggered>>
     */
    private array $testTriggeredPhpunitWarningEvents = [];

    /**
     * @var list<TestRunnerDeprecationTriggered>
     */
    private array $testRunnerTriggeredDeprecationEvents = [];

    /**
     * @var list<TestRunnerNoticeTriggered>
     */
    private array $testRunnerTriggeredNoticeEvents = [];

    /**
     * @var list<TestRunnerWarningTriggered>
     */
    private array $testRunnerTriggeredWarningEvents = [];

    /**
     * @var list<TestRunnerIssueDeprecationTriggered>
     */
    private array $testRunnerTriggeredIssueDeprecationEvents = [];

    /**
     * @var list<TestRunnerIssueErrorTriggered>
     */
    private array $testRunnerTriggeredIssueErrorEvents = [];

    /**
     * @var list<TestRunnerIssueNoticeTriggered>
     */
    private array $testRunnerTriggeredIssueNoticeEvents = [];

    /**
     * @var list<TestRunnerIssuePhpDeprecationTriggered>
     */
    private array $testRunnerTriggeredIssuePhpDeprecationEvents = [];

    /**
     * @var list<TestRunnerIssuePhpNoticeTriggered>
     */
    private array $testRunnerTriggeredIssuePhpNoticeEvents = [];

    /**
     * @var list<TestRunnerIssuePhpWarningTriggered>
     */
    private array $testRunnerTriggeredIssuePhpWarningEvents = [];

    /**
     * @var list<TestRunnerIssueWarningTriggered>
     */
    private array $testRunnerTriggeredIssueWarningEvents = [];

    /**
     * @var array<non-empty-string, Issue>
     */
    private array $errors = [];

    /**
     * @var array<non-empty-string, Issue>
     */
    private array $deprecations = [];

    /**
     * @var array<non-empty-string, Issue>
     */
    private array $notices = [];

    /**
     * @var array<non-empty-string, Issue>
     */
    private array $warnings = [];

    /**
     * @var array<non-empty-string, Issue>
     */
    private array $phpDeprecations = [];

    /**
     * @var array<non-empty-string, Issue>
     */
    private array $phpNotices = [];

    /**
     * @var array<non-empty-string, Issue>
     */
    private array $phpWarnings = [];

    /**
     * @var array{self: array<non-empty-string, true>, direct: array<non-empty-string, true>, indirect: array<non-empty-string, true>, unknown: array<non-empty-string, true>}
     */
    private array $deprecationIdsByTrigger = [
        'self'     => [],
        'direct'   => [],
        'indirect' => [],
        'unknown'  => [],
    ];

    /**
     * @var array<non-empty-string, positive-int>
     */
    private array $retriedTests = [];

    public function __construct(Facade $facade, IssueFilter $issueFilter)
    {
        $facade->registerSubscribers(
            new ExecutionStartedSubscriber($this),
            new TestSuiteSkippedSubscriber($this),
            new TestSuiteStartedSubscriber($this),
            new TestSuiteFinishedSubscriber($this),
            new TestPreparedSubscriber($this),
            new TestFinishedSubscriber($this),
            new BeforeTestClassMethodErroredSubscriber($this),
            new BeforeTestClassMethodFailedSubscriber($this),
            new AfterTestClassMethodErroredSubscriber($this),
            new AfterTestClassMethodFailedSubscriber($this),
            new TestErroredSubscriber($this),
            new TestFailedSubscriber($this),
            new TestAttemptErroredSubscriber($this),
            new TestAttemptFailedSubscriber($this),
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
            new TestTriggeredPhpunitNoticeSubscriber($this),
            new TestTriggeredPhpunitWarningSubscriber($this),
            new TestTriggeredPhpWarningSubscriber($this),
            new TestTriggeredWarningSubscriber($this),
            new TestRunnerTriggeredPhpunitDeprecationSubscriber($this),
            new TestRunnerTriggeredPhpunitNoticeSubscriber($this),
            new TestRunnerTriggeredPhpunitWarningSubscriber($this),
            new TestRunnerTriggeredIssueDeprecationSubscriber($this),
            new TestRunnerTriggeredIssueErrorSubscriber($this),
            new TestRunnerTriggeredIssueNoticeSubscriber($this),
            new TestRunnerTriggeredIssuePhpDeprecationSubscriber($this),
            new TestRunnerTriggeredIssuePhpNoticeSubscriber($this),
            new TestRunnerTriggeredIssuePhpWarningSubscriber($this),
            new TestRunnerTriggeredIssueWarningSubscriber($this),
            new ChildProcessErroredSubscriber($this),
        );

        $this->issueFilter = $issueFilter;
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
            $this->testSuiteSkippedEvents,
            $this->testSkippedEvents,
            $this->testMarkedIncompleteEvents,
            $this->testTriggeredPhpunitDeprecationEvents,
            $this->testTriggeredPhpunitErrorEvents,
            $this->testTriggeredPhpunitNoticeEvents,
            $this->testTriggeredPhpunitWarningEvents,
            $this->testRunnerTriggeredDeprecationEvents,
            $this->testRunnerTriggeredNoticeEvents,
            $this->testRunnerTriggeredWarningEvents,
            $this->testRunnerTriggeredIssueDeprecationEvents,
            $this->testRunnerTriggeredIssueErrorEvents,
            $this->testRunnerTriggeredIssueNoticeEvents,
            $this->testRunnerTriggeredIssuePhpDeprecationEvents,
            $this->testRunnerTriggeredIssuePhpNoticeEvents,
            $this->testRunnerTriggeredIssuePhpWarningEvents,
            $this->testRunnerTriggeredIssueWarningEvents,
            array_values($this->errors),
            array_values($this->deprecations),
            array_values($this->notices),
            array_values($this->warnings),
            array_values($this->phpDeprecations),
            array_values($this->phpNotices),
            array_values($this->phpWarnings),
            $this->numberOfIssuesIgnoredByBaseline,
            [
                'self'     => count($this->deprecationIdsByTrigger['self']),
                'direct'   => count($this->deprecationIdsByTrigger['direct']),
                'indirect' => count($this->deprecationIdsByTrigger['indirect']),
                'unknown'  => count($this->deprecationIdsByTrigger['unknown']),
            ],
            $this->retriedTests,
        );
    }

    public function executionStarted(ExecutionStarted $event): void
    {
        $this->numberOfTests = $event->testSuite()->count();
    }

    public function testSuiteSkipped(TestSuiteSkipped $event): void
    {
        $testSuite = $event->testSuite();

        if (!$testSuite->isForTestClass()) {
            return;
        }

        $this->testSuiteSkippedEvents[] = $event;

        $this->numberOfTestsRun += $event->testSuite()->count();
    }

    public function testSuiteStarted(TestSuiteStarted $event): void
    {
        $testSuite = $event->testSuite();

        if (!$testSuite->isForTestClass()) {
            return;
        }
    }

    public function testSuiteFinished(TestSuiteFinished $event): void
    {
        $testSuite = $event->testSuite();

        if ($testSuite->isWithName()) {
            return;
        }

        if ($testSuite->isForTestMethodWithDataProvider()) {
            assert($testSuite instanceof TestSuiteForTestMethodWithDataProvider);

            $this->registerTestMethodAsPassedIfNoRunFailedOrErrored($testSuite);

            return;
        }

        if ($testSuite->isForRepeatedTestMethod()) {
            assert($testSuite instanceof TestSuiteForRepeatedTestMethod);

            // for a repeated data set, the enclosing data provider test suite decides
            // whether the test method passed once all of its data sets have finished
            if (!$testSuite->isForDataSet()) {
                $this->registerTestMethodAsPassedIfNoRunFailedOrErrored($testSuite);
            }

            return;
        }

        if ($testSuite->isForRetriedTestMethod()) {
            // a retried test method registers itself as passed when an attempt
            // passes, a retried data set is handled by the enclosing data
            // provider test suite
            return;
        }

        if ($testSuite->isForRepeatedPhpt() || $testSuite->isForRetriedPhpt()) {
            // PHPT tests do not register themselves with PassedTests
            return;
        }

        assert($testSuite instanceof TestSuiteForTestClass);

        PassedTests::instance()->testClassPassed($testSuite->className());
    }

    public function testPrepared(): void
    {
        $this->prepared = true;
    }

    public function testFinished(Finished $event): void
    {
        $this->numberOfAssertions += $event->numberOfAssertionsPerformed();

        $this->numberOfTestsRun++;

        $this->prepared            = false;
        $this->childProcessErrored = false;
    }

    public function beforeTestClassMethodErrored(BeforeFirstTestMethodErrored $event): void
    {
        $this->testErroredEvents[] = $event;

        $this->numberOfTestsRun++;
    }

    public function beforeTestClassMethodFailed(BeforeFirstTestMethodFailed $event): void
    {
        $this->testFailedEvents[] = $event;

        $this->numberOfTestsRun++;
    }

    public function afterTestClassMethodErrored(AfterLastTestMethodErrored $event): void
    {
        $this->testErroredEvents[] = $event;
    }

    public function afterTestClassMethodFailed(AfterLastTestMethodFailed $event): void
    {
        $this->testFailedEvents[] = $event;
    }

    public function testErrored(Errored $event): void
    {
        $this->testErroredEvents[] = $event;

        $this->forgetRetriedTest($event->test());

        if ($this->childProcessErrored) {
            return;
        }

        if (!$this->prepared) {
            $this->numberOfTestsRun++;
        }
    }

    public function testFailed(Failed $event): void
    {
        $this->testFailedEvents[] = $event;

        $this->forgetRetriedTest($event->test());
    }

    public function testAttemptErrored(AttemptErrored $event): void
    {
        $this->rememberRetriedTest($event->test());
    }

    public function testAttemptFailed(AttemptFailed $event): void
    {
        $this->rememberRetriedTest($event->test());
    }

    public function testMarkedIncomplete(MarkedIncomplete $event): void
    {
        $this->testMarkedIncompleteEvents[] = $event;
    }

    public function testSkipped(TestSkipped $event): void
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
        if (!$this->issueFilter->shouldBeProcessed($event)) {
            return;
        }

        if ($event->ignoredByBaseline()) {
            $this->numberOfIssuesIgnoredByBaseline++;

            return;
        }

        $this->registerDeprecationByTrigger($event);

        $id = $this->issueId($event);

        if (!isset($this->deprecations[$id])) {
            $this->deprecations[$id] = Issue::from(
                $event->file(),
                $event->line(),
                $event->message(),
                $event->test(),
                $event->stackTrace(),
            );

            return;
        }

        $this->deprecations[$id]->triggeredBy($event->test());
    }

    public function testTriggeredPhpDeprecation(PhpDeprecationTriggered $event): void
    {
        if (!$this->issueFilter->shouldBeProcessed($event)) {
            return;
        }

        if ($event->ignoredByBaseline()) {
            $this->numberOfIssuesIgnoredByBaseline++;

            return;
        }

        $this->registerDeprecationByTrigger($event);

        $id = $this->issueId($event);

        if (!isset($this->phpDeprecations[$id])) {
            $this->phpDeprecations[$id] = Issue::from(
                $event->file(),
                $event->line(),
                $event->message(),
                $event->test(),
            );

            return;
        }

        $this->phpDeprecations[$id]->triggeredBy($event->test());
    }

    public function testTriggeredPhpunitDeprecation(PhpunitDeprecationTriggered $event): void
    {
        if (!isset($this->testTriggeredPhpunitDeprecationEvents[$event->test()->id()])) {
            $this->testTriggeredPhpunitDeprecationEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredPhpunitDeprecationEvents[$event->test()->id()][] = $event;
    }

    public function testTriggeredPhpunitNotice(PhpunitNoticeTriggered $event): void
    {
        if (!isset($this->testTriggeredPhpunitNoticeEvents[$event->test()->id()])) {
            $this->testTriggeredPhpunitNoticeEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredPhpunitNoticeEvents[$event->test()->id()][] = $event;
    }

    public function testTriggeredError(ErrorTriggered $event): void
    {
        if (!$this->issueFilter->shouldBeProcessed($event)) {
            return;
        }

        $id = $this->issueId($event);

        if (!isset($this->errors[$id])) {
            $this->errors[$id] = Issue::from(
                $event->file(),
                $event->line(),
                $event->message(),
                $event->test(),
            );

            return;
        }

        $this->errors[$id]->triggeredBy($event->test());
    }

    public function testTriggeredNotice(NoticeTriggered $event): void
    {
        if (!$this->issueFilter->shouldBeProcessed($event)) {
            return;
        }

        if ($event->ignoredByBaseline()) {
            $this->numberOfIssuesIgnoredByBaseline++;

            return;
        }

        $id = $this->issueId($event);

        if (!isset($this->notices[$id])) {
            $this->notices[$id] = Issue::from(
                $event->file(),
                $event->line(),
                $event->message(),
                $event->test(),
            );

            return;
        }

        $this->notices[$id]->triggeredBy($event->test());
    }

    public function testTriggeredPhpNotice(PhpNoticeTriggered $event): void
    {
        if (!$this->issueFilter->shouldBeProcessed($event)) {
            return;
        }

        if ($event->ignoredByBaseline()) {
            $this->numberOfIssuesIgnoredByBaseline++;

            return;
        }

        $id = $this->issueId($event);

        if (!isset($this->phpNotices[$id])) {
            $this->phpNotices[$id] = Issue::from(
                $event->file(),
                $event->line(),
                $event->message(),
                $event->test(),
            );

            return;
        }

        $this->phpNotices[$id]->triggeredBy($event->test());
    }

    public function testTriggeredWarning(WarningTriggered $event): void
    {
        if (!$this->issueFilter->shouldBeProcessed($event)) {
            return;
        }

        if ($event->ignoredByBaseline()) {
            $this->numberOfIssuesIgnoredByBaseline++;

            return;
        }

        $id = $this->issueId($event);

        if (!isset($this->warnings[$id])) {
            $this->warnings[$id] = Issue::from(
                $event->file(),
                $event->line(),
                $event->message(),
                $event->test(),
            );

            return;
        }

        $this->warnings[$id]->triggeredBy($event->test());
    }

    public function testTriggeredPhpWarning(PhpWarningTriggered $event): void
    {
        if (!$this->issueFilter->shouldBeProcessed($event)) {
            return;
        }

        if ($event->ignoredByBaseline()) {
            $this->numberOfIssuesIgnoredByBaseline++;

            return;
        }

        $id = $this->issueId($event);

        if (!isset($this->phpWarnings[$id])) {
            $this->phpWarnings[$id] = Issue::from(
                $event->file(),
                $event->line(),
                $event->message(),
                $event->test(),
            );

            return;
        }

        $this->phpWarnings[$id]->triggeredBy($event->test());
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
        if ($event->ignoredByTest()) {
            return;
        }

        if (!isset($this->testTriggeredPhpunitWarningEvents[$event->test()->id()])) {
            $this->testTriggeredPhpunitWarningEvents[$event->test()->id()] = [];
        }

        $this->testTriggeredPhpunitWarningEvents[$event->test()->id()][] = $event;
    }

    public function testRunnerTriggeredPhpunitDeprecation(TestRunnerDeprecationTriggered $event): void
    {
        $this->testRunnerTriggeredDeprecationEvents[] = $event;
    }

    public function testRunnerTriggeredPhpunitNotice(TestRunnerNoticeTriggered $event): void
    {
        $this->testRunnerTriggeredNoticeEvents[] = $event;
    }

    public function testRunnerTriggeredPhpunitWarning(TestRunnerWarningTriggered $event): void
    {
        $this->testRunnerTriggeredWarningEvents[] = $event;
    }

    public function testRunnerTriggeredIssueDeprecation(TestRunnerIssueDeprecationTriggered $event): void
    {
        if ($event->ignoredByFilter()) {
            return;
        }

        $this->registerDeprecationByTrigger($event);

        $this->testRunnerTriggeredIssueDeprecationEvents[] = $event;
    }

    public function testRunnerTriggeredIssueError(TestRunnerIssueErrorTriggered $event): void
    {
        $this->testRunnerTriggeredIssueErrorEvents[] = $event;
    }

    public function testRunnerTriggeredIssueNotice(TestRunnerIssueNoticeTriggered $event): void
    {
        $this->testRunnerTriggeredIssueNoticeEvents[] = $event;
    }

    public function testRunnerTriggeredIssuePhpDeprecation(TestRunnerIssuePhpDeprecationTriggered $event): void
    {
        if ($event->ignoredByFilter()) {
            return;
        }

        $this->registerDeprecationByTrigger($event);

        $this->testRunnerTriggeredIssuePhpDeprecationEvents[] = $event;
    }

    public function testRunnerTriggeredIssuePhpNotice(TestRunnerIssuePhpNoticeTriggered $event): void
    {
        $this->testRunnerTriggeredIssuePhpNoticeEvents[] = $event;
    }

    public function testRunnerTriggeredIssuePhpWarning(TestRunnerIssuePhpWarningTriggered $event): void
    {
        $this->testRunnerTriggeredIssuePhpWarningEvents[] = $event;
    }

    public function testRunnerTriggeredIssueWarning(TestRunnerIssueWarningTriggered $event): void
    {
        $this->testRunnerTriggeredIssueWarningEvents[] = $event;
    }

    public function childProcessErrored(ChildProcessErrored $event): void
    {
        $this->childProcessErrored = true;
    }

    public function numberOfErroredTests(): int
    {
        return count($this->testErroredEvents);
    }

    public function numberOfFailedTests(): int
    {
        return count($this->testFailedEvents);
    }

    public function numberOfRiskyTests(): int
    {
        return count($this->testConsideredRiskyEvents);
    }

    public function numberOfSkippedTests(): int
    {
        return count($this->testSkippedEvents);
    }

    public function numberOfIncompleteTests(): int
    {
        return count($this->testMarkedIncompleteEvents);
    }

    public function numberOfNotices(): int
    {
        return count($this->notices) +
               count($this->phpNotices);
    }

    public function numberOfWarnings(): int
    {
        return count($this->warnings) +
               count($this->phpWarnings) +
               count($this->testTriggeredPhpunitWarningEvents) +
               count($this->testRunnerTriggeredWarningEvents);
    }

    private function rememberRetriedTest(Test $test): void
    {
        $id = $this->retriedTestId($test);

        if ($id === null) {
            return;
        }

        if (!isset($this->retriedTests[$id])) {
            $this->retriedTests[$id] = 1;

            return;
        }

        $this->retriedTests[$id]++;
    }

    private function forgetRetriedTest(Test $test): void
    {
        $id = $this->retriedTestId($test);

        if ($id === null) {
            return;
        }

        unset($this->retriedTests[$id]);
    }

    /**
     * @return ?non-empty-string
     */
    private function retriedTestId(Test $test): ?string
    {
        if ($test->isTestMethod()) {
            assert($test instanceof TestMethod);

            return $this->logicalTestId($test);
        }

        if ($test->isPhpt()) {
            return $test->file();
        }

        return null;
    }

    /**
     * @return non-empty-string
     */
    private function logicalTestId(TestMethod $test): string
    {
        $id = $test->className() . '::' . $test->methodName();

        if ($test->testData()->hasDataFromDataProvider()) {
            $id .= '#' . $test->testData()->dataFromDataProvider()->dataSetName();
        }

        return $id;
    }

    private function registerTestMethodAsPassedIfNoRunFailedOrErrored(TestSuiteForRepeatedTestMethod|TestSuiteForTestMethodWithDataProvider $testSuite): void
    {
        assert(count($testSuite->tests()->asArray()) > 0);

        $test = $testSuite->tests()->asArray()[0];

        assert($test instanceof TestMethod);

        foreach (array_merge($this->testFailedEvents, $this->testErroredEvents) as $event) {
            if ($event instanceof AfterLastTestMethodFailed ||
                $event instanceof BeforeFirstTestMethodFailed ||
                $event instanceof AfterLastTestMethodErrored ||
                $event instanceof BeforeFirstTestMethodErrored) {
                continue;
            }

            if ($event->test()->isTestMethod() &&
                $event->test()->className() === $test->className() &&
                $event->test()->methodName() === $test->methodName()) {
                return;
            }
        }

        PassedTests::instance()->testMethodPassed($test, null);
    }

    /**
     * @return non-empty-string
     */
    private function issueId(DeprecationTriggered|ErrorTriggered|NoticeTriggered|PhpDeprecationTriggered|PhpNoticeTriggered|PhpWarningTriggered|WarningTriggered $event): string
    {
        return implode(':', [$event->file(), $event->line(), $event->message()]);
    }

    private function registerDeprecationByTrigger(DeprecationTriggered|PhpDeprecationTriggered|TestRunnerIssueDeprecationTriggered|TestRunnerIssuePhpDeprecationTriggered $event): void
    {
        $id = implode(':', [$event->file(), $event->line(), $event->message()]);

        assert($id !== '');

        if ($event->trigger()->isSelf()) {
            $this->deprecationIdsByTrigger['self'][$id] = true;

            return;
        }

        if ($event->trigger()->isDirect()) {
            $this->deprecationIdsByTrigger['direct'][$id] = true;

            return;
        }

        if ($event->trigger()->isIndirect()) {
            $this->deprecationIdsByTrigger['indirect'][$id] = true;

            return;
        }

        $this->deprecationIdsByTrigger['unknown'][$id] = true;
    }
}
