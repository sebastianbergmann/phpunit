<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Logging\TestDox;

use function array_key_exists;
use function array_keys;
use function array_merge;
use function assert;
use function is_subclass_of;
use function ksort;
use function uksort;
use function usort;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\Throwable;
use PHPUnit\Event\Event;
use PHPUnit\Event\EventFacadeIsSealedException;
use PHPUnit\Event\Facade;
use PHPUnit\Event\InvalidArgumentException;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErrorTriggered;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\MockObjectCreated;
use PHPUnit\Event\Test\MockObjectForAbstractClassCreated;
use PHPUnit\Event\Test\MockObjectForTraitCreated;
use PHPUnit\Event\Test\MockObjectFromWsdlCreated;
use PHPUnit\Event\Test\NoticeTriggered;
use PHPUnit\Event\Test\PartialMockObjectCreated;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitErrorTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\TestProxyCreated;
use PHPUnit\Event\Test\TestStubCreated;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\TestRunner\DeprecationTriggered as TestRunnerDeprecationTriggered;
use PHPUnit\Event\TestRunner\ExecutionStarted as TestRunnerExecutionStarted;
use PHPUnit\Event\TestRunner\WarningTriggered as TestRunnerWarningTriggered;
use PHPUnit\Event\TestSuite\Finished as TestSuiteFinished;
use PHPUnit\Event\TestSuite\Skipped as TestSuiteSkipped;
use PHPUnit\Event\TestSuite\Started as TestSuiteStarted;
use PHPUnit\Event\UnknownSubscriberTypeException;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Logging\TestDox\TestResult as TestDoxTestMethod;
use ReflectionMethod;
use SoapClient;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class TestResultCollector
{
    /**
     * @psalm-var array<string, list<TestDoxTestMethod>>
     */
    private array $tests          = [];
    private ?HRTime $time         = null;
    private ?TestStatus $status   = null;
    private ?Throwable $throwable = null;

    /**
     * @psalm-var list<class-string|trait-string>
     */
    private array $testDoubles = [];

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    public function __construct(Facade $facade)
    {
        $this->registerSubscribers($facade);
    }

    public function __invoke(Event $event): void
    {
        /** @var ?TestStatus $status */
        $status = $this->getStatus($event);

        if (!$status instanceof TestStatus) {
            return;
        }

        /**
         * @var ?HRTime $time
         *
         * @psalm-suppress UnsupportedPropertyReferenceUsage
         */
        $time = &$this->time;

        if (!$time instanceof HRTime) {
            $time = $event->telemetryInfo()->time();
        }

        /**
         * @var TestStatus $currentStatus
         *
         * @psalm-suppress UnsupportedPropertyReferenceUsage
         */
        $currentStatus = &$this->status;

        if (!$currentStatus instanceof TestStatus) {

            $currentStatus = $status;

            return;
        }

        if ($currentStatus->isMoreImportantThan($status)) {
            return;
        }

        $currentStatus = $status;
    }

    /**
     * @psalm-return array<string, TestResultCollection>
     */
    public function testMethodsGroupedByClass(): array
    {
        $result = [];

        foreach ($this->tests as $prettifiedClassName => $tests) {
            $testsByDeclaringClass = [];

            foreach ($tests as $test) {
                $declaringClassName = (new ReflectionMethod($test->test()->className(), $test->test()->methodName()))->getDeclaringClass()->getName();

                if (!isset($testsByDeclaringClass[$declaringClassName])) {
                    $testsByDeclaringClass[$declaringClassName] = [];
                }

                $testsByDeclaringClass[$declaringClassName][] = $test;
            }

            foreach (array_keys($testsByDeclaringClass) as $declaringClassName) {
                usort(
                    $testsByDeclaringClass[$declaringClassName],
                    static function (TestDoxTestMethod $a, TestDoxTestMethod $b): int
                    {
                        return $a->test()->line() <=> $b->test()->line();
                    },
                );
            }

            uksort(
                $testsByDeclaringClass,
                /**
                 * @psalm-param class-string $a
                 * @psalm-param class-string $b
                 */
                static function (string $a, string $b): int
                {
                    if (is_subclass_of($b, $a)) {
                        return -1;
                    }

                    if (is_subclass_of($a, $b)) {
                        return 1;
                    }

                    return 0;
                },
            );

            $tests = [];

            foreach ($testsByDeclaringClass as $_tests) {
                $tests = array_merge($tests, $_tests);
            }

            $result[$prettifiedClassName] = TestResultCollection::fromArray($tests);
        }

        ksort($result);

        return $result;
    }

    public function testPrepared(Prepared $event): void
    {
        if (!$event->test()->isTestMethod()) {
            return;
        }

        $this->throwable   = null;
        $this->testDoubles = [];
        $this($event);
    }

    public function testErrored(Errored $event): void
    {
        if (!$event->test()->isTestMethod()) {
            return;
        }

        $this->throwable = $event->throwable();

        $this($event);
    }

    public function testFailed(Failed $event): void
    {
        if (!$event->test()->isTestMethod()) {
            return;
        }

        $this->throwable = $event->throwable();

        $this($event);
    }

    public function testPassed(Passed $event): void
    {
        if (!$event->test()->isTestMethod()) {
            return;
        }

        $this($event);
    }

    public function testSkipped(Skipped $event): void
    {
        $this($event);
    }

    public function testMarkedIncomplete(MarkedIncomplete $event): void
    {
        $this->throwable = $event->throwable();

        $this($event);
    }

    public function testConsideredRisky(ConsideredRisky $event): void
    {
        $this($event);
    }

    public function testCreatedTestDouble(MockObjectCreated|MockObjectForAbstractClassCreated|MockObjectForTraitCreated|MockObjectFromWsdlCreated|PartialMockObjectCreated|TestProxyCreated|TestStubCreated $event): void
    {
        if ($event instanceof MockObjectForTraitCreated) {
            $this->testDoubles[] = $event->traitName();

            return;
        }

        if ($event instanceof MockObjectFromWsdlCreated) {
            $this->testDoubles[] = SoapClient::class;

            return;
        }

        $this->testDoubles[] = $event->className();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testFinished(Finished $event): void
    {
        if (!$event->test()->isTestMethod()) {
            return;
        }

        $test = $event->test();

        assert($test instanceof TestMethod);

        $prettifiedClassName = $test->testDox()->prettifiedClassName();

        if (!array_key_exists($prettifiedClassName, $this->tests)) {
            $this->tests[$prettifiedClassName] = [];
        }

        $this->tests[$prettifiedClassName][] = new TestDoxTestMethod(
            $test,
            $event->telemetryInfo()->time()->duration($this->time),
            $this->status,
            $this->throwable,
            $this->testDoubles,
        );

        $this->time        = null;
        $this->status      = null;
        $this->throwable   = null;
        $this->testDoubles = [];
    }

    public function testTriggeredWarning(WarningTriggered $event): void
    {
        $this($event);
    }

    public function testTriggeredNotice(NoticeTriggered $event): void
    {
        $this($event);
    }

    public function testTriggeredError(ErrorTriggered $event): void
    {
        $this($event);
    }

    public function testTriggeredDeprecation(DeprecationTriggered $event): void
    {
        $this($event);
    }

    public function testRunnerTriggeredDeprecation(TestRunnerDeprecationTriggered $event): void
    {
        $this($event);
    }

    public function executionStarted(TestRunnerExecutionStarted $event): void
    {
        $this($event);
    }

    public function beforeTestClassMethodErrored(BeforeFirstTestMethodErrored $event): void
    {
        $this($event);
    }

    public function testRunnerTriggeredWarning(TestRunnerWarningTriggered $event): void
    {
        $this($event);
    }

    public function testSuiteFinished(TestSuiteFinished $event): void
    {
        $this($event);
    }

    public function testSuiteSkipped(TestSuiteSkipped $event): void
    {
        $this($event);
    }

    public function testSuiteStarted(TestSuiteStarted $event): void
    {
        $this($event);
    }

    public function testTriggeredPhpDeprecation(PhpDeprecationTriggered $event): void
    {
        $this($event);
    }

    public function testTriggeredPhpNotice(PhpNoticeTriggered $event): void
    {
        $this($event);
    }

    public function testTriggeredPhpWarning(PhpWarningTriggered $event): void
    {
        $this($event);
    }

    public function testTriggeredPhpunitWarning(PhpunitWarningTriggered $event): void
    {
        $this($event);
    }

    public function testTriggeredPhpunitError(PhpunitErrorTriggered $event): void
    {
        $this($event);
    }

    public function testTriggeredPhpunitDeprecation(PhpunitDeprecationTriggered $event): void
    {
        $this($event);
    }

    private function getStatus(Event $event): ?TestStatus
    {
        /** @var ConsideredRisky|DeprecationTriggered|Errored|ErrorTriggered|Failed|Finished|MarkedIncomplete|NoticeTriggered|Passed|PhpDeprecationTriggered|PhpNoticeTriggered|PhpunitDeprecationTriggered|PhpunitErrorTriggered|PhpunitWarningTriggered|PhpWarningTriggered|Prepared|Skipped|TestRunnerDeprecationTriggered|TestRunnerExecutionStarted|TestSuiteFinished|TestSuiteStarted|WarningTriggered $event */
        return match (true) {
            $event instanceof ConsideredRisky => TestStatus::risky($event->message()),
            $event instanceof DeprecationTriggered,
            $event instanceof PhpDeprecationTriggered,
            $event instanceof PhpunitDeprecationTriggered ,
            $event instanceof TestRunnerDeprecationTriggered => TestStatus::deprecation($event->message()),
            $event instanceof Errored                        => TestStatus::error($event->throwable()->message()),
            $event instanceof ErrorTriggered,
            $event instanceof PhpunitErrorTriggered => TestStatus::error($event->message()),
            $event instanceof Failed                => TestStatus::failure($event->throwable()->message()),
            $event instanceof MarkedIncomplete      => TestStatus::incomplete($event->throwable()->message()),
            $event instanceof NoticeTriggered,
            $event instanceof PhpNoticeTriggered => TestStatus::notice($event->message()),
            $event instanceof Passed             => TestStatus::success(),
            $event instanceof WarningTriggered,
            $event instanceof PhpunitWarningTriggered,
            $event instanceof PhpWarningTriggered => TestStatus::warning($event->message()),
            $event instanceof Prepared            => TestStatus::unknown(),
            $event instanceof Skipped             => TestStatus::skipped($event->message()),
            default                               => null,
        };
    }

    /**
     * @throws EventFacadeIsSealedException
     * @throws UnknownSubscriberTypeException
     */
    private function registerSubscribers(Facade $facade): void
    {
        $facade->registerSubscribers(
            new BeforeTestClassMethodErroredSubscriber($this),
            new ExecutionStartedSubscriber($this),
            new TestConsideredRiskySubscriber($this),
            new TestCreatedMockObjectForAbstractClassSubscriber($this),
            new TestCreatedMockObjectForTraitSubscriber($this),
            new TestCreatedMockObjectFromWsdlSubscriber($this),
            new TestCreatedMockObjectSubscriber($this),
            new TestCreatedPartialMockObjectSubscriber($this),
            new TestCreatedTestProxySubscriber($this),
            new TestCreatedTestStubSubscriber($this),
            new TestErroredSubscriber($this),
            new TestFailedSubscriber($this),
            new TestFinishedSubscriber($this),
            new TestMarkedIncompleteSubscriber($this),
            new TestPassedSubscriber($this),
            new TestPreparedSubscriber($this),
            new TestRunnerTriggeredDeprecationSubscriber($this),
            new TestRunnerTriggeredWarningSubscriber($this),
            new TestSkippedSubscriber($this),
            new TestSuiteFinishedSubscriber($this),
            new TestSuiteSkippedSubscriber($this),
            new TestSuiteStartedSubscriber($this),
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
        );
    }
}
