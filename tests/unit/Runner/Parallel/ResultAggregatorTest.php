<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Parallel;

use function hrtime;
use function serialize;
use PHPUnit\Event\Code\Test as CodeTest;
use PHPUnit\Event\Code\TestMethodBuilder;
use PHPUnit\Event\Code\Throwable as CodeThrowable;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Telemetry;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Test\Finished as TestFinished;
use PHPUnit\Event\TestRunner\ChildProcessReason;
use PHPUnit\Event\TestRunner\WarningTriggered;
use PHPUnit\Event\TestRunner\WarningTriggeredSubscriber;
use PHPUnit\Event\TestSuite\Finished as TestSuiteFinishedEvent;
use PHPUnit\Event\TestSuite\Started as TestSuiteStarted;
use PHPUnit\Event\TestSuite\TestSuite as TestSuiteValue;
use PHPUnit\Event\TestSuite\TestSuiteBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\DataProviderTestSuite;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite as FrameworkTestSuite;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestFixture\ParallelWorker\WorkerFirstTest;
use PHPUnit\TestFixture\ParallelWorker\WorkerSecondTest;
use PHPUnit\TestRunner\TestResult\PassedTests;

#[CoversClass(ResultAggregator::class)]
#[UsesClass(TestClassWorkUnit::class)]
#[UsesClass(CompletedWorkUnit::class)]
#[Small]
final class ResultAggregatorTest extends TestCase
{
    public function testForwardsAndImportsAFinishedUnit(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter->expects($this->never())->method('childProcessErrored');
        $emitter->expects($this->never())->method('testRunnerTriggeredPhpunitWarning')->seal();

        $aggregator = $this->aggregator($emitter);

        $nonce = 'abc';

        $aggregator->add(
            CompletedWorkUnit::fromEnvelope(
                new TestClassWorkUnit(0, self::class, []),
                $nonce . serialize((object) ['codeCoverage' => null, 'events' => new EventCollection, 'passedTests' => new PassedTests]),
                $nonce,
            ),
        );
    }

    public function testReportsTheTestsOfACrashedUnitAsErroredInsideASynthesizedSuiteEnvelope(): void
    {
        $emitter = $this->createMock(Emitter::class);

        // Each stubbed test is reported with the same three events that the
        // sequential runner emits for a test whose child process ended
        // unexpectedly: childProcessErrored, testErrored, testFinished.
        $emitter->expects($this->exactly(2))
            ->method('childProcessErrored')
            ->with($this->anything(), $this->stringContains('ended unexpectedly'));
        $emitter->expects($this->never())->method('testRunnerTriggeredPhpunitWarning');

        // No event of the crashed unit was forwarded, so the class-level
        // envelope is synthesized around the errored tests, as the unit
        // itself would have emitted it.
        $emitter->expects($this->once())->method('testSuiteStarted');
        $emitter->expects($this->exactly(2))->method('testFinished');
        $emitter->expects($this->once())->method('testSuiteFinished');

        $errored = [];

        $emitter->method('testErrored')->willReturnCallback(
            static function (CodeTest $test, CodeThrowable $throwable) use (&$errored): void
            {
                $errored[] = $test->id();
            },
        )->seal();

        $this->aggregator($emitter)->add(
            CompletedWorkUnit::fromCrash(
                new TestClassWorkUnit(
                    0,
                    WorkerSecondTest::class,
                    [
                        new WorkerSecondTest('testSeesTheStateLeftBehindByTheFirstTest'),
                        new WorkerSecondTest('testThatFails'),
                    ],
                ),
            ),
        );

        $this->assertSame(
            [
                WorkerSecondTest::class . '::testSeesTheStateLeftBehindByTheFirstTest',
                WorkerSecondTest::class . '::testThatFails',
            ],
            $errored,
        );
    }

    public function testClosesTheForwardedEnvelopeAndReportsOnlyTheUnreportedTestsOfACrashedUnit(): void
    {
        $reported   = new WorkerSecondTest('testSeesTheStateLeftBehindByTheFirstTest');
        $unreported = new WorkerSecondTest('testThatFails');

        $frameworkSuite = FrameworkTestSuite::empty(WorkerSecondTest::class);

        $frameworkSuite->addTest($reported);
        $frameworkSuite->addTest($unreported);

        $suiteValue = TestSuiteBuilder::from($frameworkSuite);

        $emitter = $this->createMock(Emitter::class);

        // The class-level envelope was already opened by the forwarded frame,
        // so it must not be opened a second time — but it must be closed, with
        // the very suite that opened it.
        $emitter->expects($this->never())->method('testSuiteStarted');
        $emitter->expects($this->once())
            ->method('testSuiteFinished')
            ->with($this->identicalTo($suiteValue));
        $emitter->expects($this->once())->method('childProcessErrored');
        $emitter->expects($this->once())->method('testFinished');

        $errored = [];

        $emitter->method('testErrored')->willReturnCallback(
            static function (CodeTest $test, CodeThrowable $throwable) use (&$errored): void
            {
                $errored[] = $test->id();
            },
        )->seal();

        $forwarded = [];

        $aggregator = $this->aggregatorObservedThrough($forwarded, $emitter);

        // The unit at index 0 streams a frame in which its class-level suite
        // envelope opens and its first test finishes; the frame is forwarded
        // live. Then the worker dies before the second test finishes.
        $frame = new EventCollection;

        $frame->add(new TestSuiteStarted($this->telemetryInfo(), $suiteValue));
        $frame->add(new TestFinished($this->telemetryInfo(), TestMethodBuilder::fromTestCase($reported), 1));

        $aggregator->addStreamedEvents(0, $frame);

        $aggregator->add(
            CompletedWorkUnit::fromCrash(
                new TestClassWorkUnit(0, WorkerSecondTest::class, [$reported, $unreported]),
            ),
        );

        // Only the test whose result never arrived is reported as errored;
        // the test that was already reported through the forwarded frame is
        // not reported a second time.
        $this->assertSame([WorkerSecondTest::class . '::testThatFails'], $errored);
    }

    public function testClosesTheOpenEnvelopeOfADataProviderMemberBeforeReportingTheTestsThatFollowIt(): void
    {
        // Two provider cases that share one test id: the frames report two
        // finishes of that id, and each finish excuses exactly one of them.
        $firstCase  = new WorkerSecondTest('testThatFails');
        $secondCase = new WorkerSecondTest('testThatFails');

        $providerSuite = DataProviderTestSuite::empty(WorkerSecondTest::class . '::testThatFails');

        $providerSuite->addTest($firstCase);
        $providerSuite->addTest($secondCase);

        $trailingTest = new WorkerSecondTest('testSeesTheStateLeftBehindByTheFirstTest');

        $frameworkSuite = FrameworkTestSuite::empty(WorkerSecondTest::class);

        $frameworkSuite->addTest($providerSuite);
        $frameworkSuite->addTest($trailingTest);

        $classValue    = TestSuiteBuilder::from($frameworkSuite);
        $providerValue = TestSuiteBuilder::from($providerSuite);

        $emitter = $this->createMock(Emitter::class);

        $emitter->expects($this->never())->method('testSuiteStarted');
        $emitter->expects($this->once())->method('childProcessErrored');
        $emitter->expects($this->once())->method('testFinished');

        $errored = [];

        $emitter->method('testErrored')->willReturnCallback(
            static function (CodeTest $test, CodeThrowable $throwable) use (&$errored): void
            {
                $errored[] = $test->id();
            },
        );

        $closed = [];

        $emitter->method('testSuiteFinished')->willReturnCallback(
            static function (TestSuiteValue $testSuite) use (&$closed): void
            {
                $closed[] = $testSuite->name();
            },
        )->seal();

        $forwarded = [];

        $aggregator = $this->aggregatorObservedThrough($forwarded, $emitter);

        // The streamed frame opens the class-level and the data-provider
        // envelopes and finishes both provider cases; the provider envelope's
        // closing event never arrives because the worker dies before the
        // trailing test finishes.
        $frame = new EventCollection;

        $frame->add(new TestSuiteStarted($this->telemetryInfo(), $classValue));
        $frame->add(new TestSuiteStarted($this->telemetryInfo(), $providerValue));
        $frame->add(new TestFinished($this->telemetryInfo(), TestMethodBuilder::fromTestCase($firstCase), 1));
        $frame->add(new TestFinished($this->telemetryInfo(), TestMethodBuilder::fromTestCase($secondCase), 1));

        $aggregator->addStreamedEvents(0, $frame);

        $aggregator->add(
            CompletedWorkUnit::fromCrash(
                new TestClassWorkUnit(0, WorkerSecondTest::class, [$providerSuite, $trailingTest]),
            ),
        );

        // Only the trailing test is reported as errored; the provider
        // envelope is closed before it, so it is not nested inside the
        // provider suite, and the class envelope is closed last.
        $this->assertSame([WorkerSecondTest::class . '::testSeesTheStateLeftBehindByTheFirstTest'], $errored);
        $this->assertSame(
            [
                WorkerSecondTest::class . '::testThatFails',
                WorkerSecondTest::class,
            ],
            $closed,
        );
    }

    public function testDoesNotCloseAnEnvelopeThatAForwardedFrameAlreadyClosed(): void
    {
        $providerCase = new WorkerSecondTest('testThatFails');

        $providerSuite = DataProviderTestSuite::empty(WorkerSecondTest::class . '::testThatFails');

        $providerSuite->addTest($providerCase);

        $frameworkSuite = FrameworkTestSuite::empty(WorkerSecondTest::class);

        $frameworkSuite->addTest($providerSuite);

        $classValue    = TestSuiteBuilder::from($frameworkSuite);
        $providerValue = TestSuiteBuilder::from($providerSuite);

        $emitter = $this->createMock(Emitter::class);

        $emitter->expects($this->never())->method('testSuiteStarted');
        $emitter->expects($this->never())->method('testErrored');

        // The unit's only test was already reported, so the crash is
        // signalled without stubbing any test, and only the class envelope,
        // which the frames left open, is closed.
        $emitter->expects($this->once())->method('childProcessErrored');
        $emitter->expects($this->once())
            ->method('testSuiteFinished')
            ->with($this->identicalTo($classValue))
            ->seal();

        $forwarded = [];

        $aggregator = $this->aggregatorObservedThrough($forwarded, $emitter);

        $frame = new EventCollection;

        $frame->add(new TestSuiteStarted($this->telemetryInfo(), $classValue));
        $frame->add(new TestSuiteStarted($this->telemetryInfo(), $providerValue));
        $frame->add(new TestFinished($this->telemetryInfo(), TestMethodBuilder::fromTestCase($providerCase), 1));
        $frame->add(new TestSuiteFinishedEvent($this->telemetryInfo(), $providerValue));

        $aggregator->addStreamedEvents(0, $frame);

        $aggregator->add(
            CompletedWorkUnit::fromCrash(
                new TestClassWorkUnit(0, WorkerSecondTest::class, [$providerSuite]),
            ),
        );
    }

    public function testRejectsAResultWhoseNonceDoesNotMatch(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter->expects($this->once())
            ->method('childProcessErrored')
            ->with($this->anything(), $this->stringContains('tampered with'));
        $emitter->expects($this->never())->method('testRunnerTriggeredPhpunitWarning');
        $emitter->expects($this->once())->method('testSuiteStarted');
        $emitter->expects($this->once())->method('testSuiteFinished')->seal();

        $this->aggregator($emitter)->add(
            CompletedWorkUnit::fromEnvelope(new TestClassWorkUnit(0, self::class, []), 'expected-nonce' . serialize((object) []), 'actual-nonce'),
        );
    }

    public function testRejectsAMalformedResult(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter->expects($this->once())
            ->method('childProcessErrored')
            ->with($this->anything(), $this->stringContains('ended unexpectedly'));
        $emitter->expects($this->never())->method('testRunnerTriggeredPhpunitWarning');
        $emitter->expects($this->once())->method('testSuiteStarted');
        $emitter->expects($this->once())->method('testSuiteFinished')->seal();

        $nonce = 'abc';

        $this->aggregator($emitter)->add(
            CompletedWorkUnit::fromEnvelope(new TestClassWorkUnit(0, self::class, []), $nonce . 'not-a-serialized-envelope', $nonce),
        );
    }

    public function testForwardsBufferedUnitsInSuiteOrderRegardlessOfCompletionOrder(): void
    {
        $messages = [];

        $emitter = $this->createMock(Emitter::class);

        $emitter->method('testSuiteStarted');
        $emitter->method('testSuiteFinished');
        $emitter->method('childProcessErrored')->willReturnCallback(
            static function (ChildProcessReason $reason, string $message) use (&$messages): void
            {
                $messages[] = $message;
            },
        )->seal();

        $aggregator = $this->aggregator($emitter);

        // The unit at index 1 finishes first; it must not be forwarded until
        // the unit at index 0, which precedes it in suite order, has been
        // forwarded.
        $aggregator->add(CompletedWorkUnit::fromCrash(new TestClassWorkUnit(1, WorkerSecondTest::class, [])));

        $this->assertSame([], $messages);

        $aggregator->add(CompletedWorkUnit::fromCrash(new TestClassWorkUnit(0, WorkerFirstTest::class, [])));

        $this->assertCount(2, $messages);
        $this->assertStringContainsString(WorkerFirstTest::class, $messages[0]);
        $this->assertStringContainsString(WorkerSecondTest::class, $messages[1]);
    }

    public function testRunsInProcessUnitsAtTheirIndexInGlobalSuiteOrder(): void
    {
        $order = [];

        $emitter = $this->createMock(Emitter::class);

        $emitter->method('testSuiteStarted');
        $emitter->method('testSuiteFinished');
        $emitter->method('childProcessErrored')->willReturnCallback(
            static function (ChildProcessReason $reason, string $message) use (&$order): void
            {
                $order[] = $message;
            },
        )->seal();

        $aggregator = $this->aggregator($emitter);

        // The unit at index 1 runs in the main process; the units at index 0
        // and 2 run in workers and finish out of order.
        $aggregator->registerInProcessUnit(
            1,
            static function () use (&$order): void
            {
                $order[] = 'in-process';
            },
        );

        // The worker unit at index 2 finishes first and must be held back.
        $aggregator->add(CompletedWorkUnit::fromCrash(new TestClassWorkUnit(2, WorkerSecondTest::class, [])));

        $this->assertSame([], $order);

        // Once index 0 arrives, index 0 is forwarded, then the in-process unit
        // at index 1 is run in place, then index 2 is released — global order.
        $aggregator->add(CompletedWorkUnit::fromCrash(new TestClassWorkUnit(0, WorkerFirstTest::class, [])));

        $this->assertCount(3, $order);
        $this->assertStringContainsString(WorkerFirstTest::class, $order[0]);
        $this->assertSame('in-process', $order[1]);
        $this->assertStringContainsString(WorkerSecondTest::class, $order[2]);
    }

    public function testStopsTheReleaseSequenceAtAnExclusiveUnitUntilItIsRunExplicitly(): void
    {
        $order = [];

        $aggregator = $this->aggregator($this->createStub(Emitter::class));

        $aggregator->registerInProcessUnit(
            0,
            static function () use (&$order): void
            {
                $order[] = 'exclusive';
            },
            true,
        );

        $aggregator->registerInProcessUnit(
            1,
            static function () use (&$order): void
            {
                $order[] = 'ordinary';
            },
        );

        // The release sequence stops at the exclusive unit instead of running
        // it — the unit must run alone, and only the caller knows when
        // nothing else is executing. The ordinary unit behind it waits, too.
        $aggregator->flush();

        $this->assertSame([], $order);
        $this->assertTrue($aggregator->hasPendingExclusiveUnit());

        // Running the pending exclusive unit resumes the release sequence,
        // which then releases the ordinary unit behind it.
        $aggregator->runPendingExclusiveUnit();

        $this->assertSame(['exclusive', 'ordinary'], $order);
        $this->assertFalse($aggregator->hasPendingExclusiveUnit());
    }

    public function testRunsRegisteredInProcessUnitsThatPrecedeAllWorkerUnitsOnFlush(): void
    {
        $order = [];

        $aggregator = $this->aggregator($this->createStub(Emitter::class));

        $aggregator->registerInProcessUnit(
            0,
            static function () use (&$order): void
            {
                $order[] = 'in-process';
            },
        );

        // No worker completion drives the release, so an explicit flush() must
        // run the leading in-process unit.
        $this->assertSame([], $order);

        $aggregator->flush();

        $this->assertSame(['in-process'], $order);
    }

    public function testForwardsTheStreamedEventsOfTheUnitThatIsNextInSuiteOrderImmediately(): void
    {
        $forwarded = [];

        $aggregator = $this->aggregatorObservedThrough($forwarded, $this->createStub(Emitter::class));

        // The unit at index 0 is what the ordered output waits for, so the
        // events it streams while it is still running are forwarded live.
        $aggregator->addStreamedEvents(0, $this->events('first test of unit 0'));

        $this->assertSame(['first test of unit 0'], $forwarded);
    }

    public function testBuffersTheStreamedEventsOfALaterUnitUntilItsTurnComes(): void
    {
        $forwarded = [];

        $emitter = $this->createMock(Emitter::class);

        $emitter->method('testSuiteStarted');
        $emitter->method('testSuiteFinished');
        $emitter->method('childProcessErrored')->willReturnCallback(
            static function (ChildProcessReason $reason, string $message) use (&$forwarded): void
            {
                $forwarded[] = $message;
            },
        )->seal();

        $aggregator = $this->aggregatorObservedThrough($forwarded, $emitter);

        // The unit at index 1 streams events while the unit at index 0 is
        // still running; they must be held back.
        $aggregator->addStreamedEvents(1, $this->events('first test of unit 1'));

        $this->assertSame([], $forwarded);

        // The unit at index 0 finishes (as a crash, whose report marks its
        // place in the forwarded sequence); the buffered events of the unit at
        // index 1 follow it immediately, and events the unit streams from now
        // on are forwarded live.
        $aggregator->add(CompletedWorkUnit::fromCrash(new TestClassWorkUnit(0, WorkerFirstTest::class, [])));

        $aggregator->addStreamedEvents(1, $this->events('second test of unit 1'));

        $this->assertCount(3, $forwarded);
        $this->assertStringContainsString(WorkerFirstTest::class, $forwarded[0]);
        $this->assertSame('first test of unit 1', $forwarded[1]);
        $this->assertSame('second test of unit 1', $forwarded[2]);
    }

    public function testForwardsTheStreamedEventsOfACrashedUnitBeforeReportingTheCrash(): void
    {
        $forwarded = [];

        $emitter = $this->createMock(Emitter::class);

        $emitter->method('testSuiteStarted');
        $emitter->method('testSuiteFinished');
        $emitter->method('childProcessErrored')->willReturnCallback(
            static function (ChildProcessReason $reason, string $message) use (&$forwarded): void
            {
                $forwarded[] = $message;
            },
        )->seal();

        $aggregator = $this->aggregatorObservedThrough($forwarded, $emitter);

        // The unit at index 1 streams the events of a test that completed and
        // then crashes, all while the unit at index 0 is still running.
        $aggregator->addStreamedEvents(1, $this->events('completed test of unit 1'));
        $aggregator->add(CompletedWorkUnit::fromCrash(new TestClassWorkUnit(1, WorkerSecondTest::class, [])));

        $this->assertSame([], $forwarded);

        $aggregator->add(CompletedWorkUnit::fromCrash(new TestClassWorkUnit(0, WorkerFirstTest::class, [])));

        $this->assertCount(3, $forwarded);
        $this->assertStringContainsString(WorkerFirstTest::class, $forwarded[0]);
        $this->assertSame('completed test of unit 1', $forwarded[1]);
        $this->assertStringContainsString(WorkerSecondTest::class, $forwarded[2]);
    }

    public function testDiscardsTheBufferedStreamedEventsOfAUnitThatIsToBeRetried(): void
    {
        $forwarded = [];

        $emitter = $this->createMock(Emitter::class);

        $emitter->method('testSuiteStarted');
        $emitter->method('testSuiteFinished');
        $emitter->method('childProcessErrored')->willReturnCallback(
            static function (ChildProcessReason $reason, string $message) use (&$forwarded): void
            {
                $forwarded[] = $message;
            },
        )->seal();

        $aggregator = $this->aggregatorObservedThrough($forwarded, $emitter);

        // The unit at index 1 streamed an event, buffered behind index 0,
        // and then its worker crashed: the retry is allowed, and the crashed
        // attempt's buffered event is discarded.
        $aggregator->addStreamedEvents(1, $this->events('first attempt'));

        $this->assertTrue($aggregator->discardStreamedEventsFor(1));

        // The retry streams its own event and both units finish (as crashes,
        // whose reports mark their places in the forwarded sequence): the
        // discarded event must not appear, the retry's event must.
        $aggregator->addStreamedEvents(1, $this->events('second attempt'));

        $aggregator->add(CompletedWorkUnit::fromCrash(new TestClassWorkUnit(0, WorkerFirstTest::class, [])));
        $aggregator->add(CompletedWorkUnit::fromCrash(new TestClassWorkUnit(1, WorkerSecondTest::class, [])));

        $this->assertCount(3, $forwarded);
        $this->assertStringContainsString(WorkerFirstTest::class, $forwarded[0]);
        $this->assertSame('second attempt', $forwarded[1]);
        $this->assertStringContainsString(WorkerSecondTest::class, $forwarded[2]);
    }

    public function testDoesNotAllowARetryForAUnitWhoseStreamedEventsWereForwarded(): void
    {
        $forwarded = [];

        $aggregator = $this->aggregatorObservedThrough($forwarded, $this->createStub(Emitter::class));

        // The unit at index 0 is next in suite order, so its streamed events
        // were forwarded live; re-running it would report them twice.
        $aggregator->addStreamedEvents(0, $this->events('already reported'));

        $this->assertSame(['already reported'], $forwarded);
        $this->assertFalse($aggregator->discardStreamedEventsFor(0));
    }

    public function testFreezesTheReleaseSequenceWhenTheCollectedResultsCallForTheRunToStop(): void
    {
        $messages = [];

        $emitter = $this->createMock(Emitter::class);

        $emitter->method('testSuiteStarted');
        $emitter->method('testSuiteFinished');
        $emitter->method('childProcessErrored')->willReturnCallback(
            static function (ChildProcessReason $reason, string $message) use (&$messages): void
            {
                $messages[] = $message;
            },
        )->seal();

        $shouldStop = false;

        $aggregator = $this->aggregator(
            $emitter,
            static function () use (&$shouldStop): bool
            {
                return $shouldStop;
            },
        );

        $aggregator->add(CompletedWorkUnit::fromCrash(new TestClassWorkUnit(0, WorkerFirstTest::class, [])));

        $this->assertCount(1, $messages);

        // The run is to stop: the unit at index 1 is not released, neither by
        // its own arrival nor by an explicit flush — its tests are ones that
        // a sequential run would not have run.
        $shouldStop = true;

        $aggregator->add(CompletedWorkUnit::fromCrash(new TestClassWorkUnit(1, WorkerSecondTest::class, [])));
        $aggregator->flush();

        $this->assertCount(1, $messages);
    }

    public function testBuffersStreamedEventsInsteadOfForwardingThemWhenTheRunIsToStop(): void
    {
        $forwarded = [];

        $shouldStop = false;

        $aggregator = $this->aggregatorObservedThrough(
            $forwarded,
            $this->createStub(Emitter::class),
            static function () use (&$shouldStop): bool
            {
                return $shouldStop;
            },
        );

        $aggregator->addStreamedEvents(0, $this->events('before the stop'));

        $this->assertSame(['before the stop'], $forwarded);

        $shouldStop = true;

        $aggregator->addStreamedEvents(0, $this->events('after the stop'));

        $this->assertSame(['before the stop'], $forwarded);
    }

    private function aggregator(Emitter $emitter, ?callable $shouldStop = null): ResultAggregator
    {
        if ($shouldStop === null) {
            $shouldStop = static function (): bool
            {
                return false;
            };
        }

        return new ResultAggregator(
            new Facade,
            $emitter,
            new PassedTests,
            new CodeCoverage,
            $shouldStop,
        );
    }

    /**
     * An aggregator whose forwarded events are observable: the message of
     * every forwarded test runner warning event is appended to $forwarded.
     * Forwarding a crashed unit records its child-process-errored message
     * through the emitter into the same array, so the order of events and crash reports
     * relative to each other is observable, too.
     *
     * @param list<string> $forwarded
     */
    private function aggregatorObservedThrough(array &$forwarded, Emitter $emitter, ?callable $shouldStop = null): ResultAggregator
    {
        if ($shouldStop === null) {
            $shouldStop = static function (): bool
            {
                return false;
            };
        }

        $facade = new Facade;

        $facade->registerSubscriber(
            new class($forwarded) implements WarningTriggeredSubscriber
            {
                /**
                 * @var list<string>
                 */
                private array $forwarded;

                /**
                 * @param list<string> $forwarded
                 */
                public function __construct(array &$forwarded)
                {
                    $this->forwarded = &$forwarded;
                }

                public function notify(WarningTriggered $event): void
                {
                    $this->forwarded[] = $event->message();
                }
            },
        );

        $facade->seal();

        return new ResultAggregator(
            $facade,
            $emitter,
            new PassedTests,
            new CodeCoverage,
            $shouldStop,
        );
    }

    private function events(string $message): EventCollection
    {
        $events = new EventCollection;

        $events->add(
            new WarningTriggered(
                $this->telemetryInfo(),
                $message,
            ),
        );

        return $events;
    }

    private function telemetryInfo(): Telemetry\Info
    {
        return new Telemetry\Info(
            new Telemetry\Snapshot(
                HRTime::fromSecondsAndNanoseconds(...hrtime(false)),
                Telemetry\MemoryUsage::fromBytes(1000),
                Telemetry\MemoryUsage::fromBytes(2000),
                new Telemetry\GarbageCollectorStatus(0, 0, 0, 0, 0.0, 0.0, 0.0, 0.0, false, false, false, 0),
                Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
                Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
                Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            ),
            Telemetry\Duration::fromSecondsAndNanoseconds(123, 456),
            Telemetry\MemoryUsage::fromBytes(2000),
            Telemetry\Duration::fromSecondsAndNanoseconds(234, 567),
            Telemetry\MemoryUsage::fromBytes(3000),
            Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
            Telemetry\CpuTime::fromSecondsAndNanoseconds(0, 0),
        );
    }
}
