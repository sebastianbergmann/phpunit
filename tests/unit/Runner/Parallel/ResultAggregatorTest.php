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
use PHPUnit\Event\Emitter;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Telemetry;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\TestRunner\WarningTriggered;
use PHPUnit\Event\TestRunner\WarningTriggeredSubscriber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
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
        $emitter->expects($this->never())->method('testRunnerTriggeredPhpunitWarning');

        $aggregator = $this->aggregator($emitter);

        $nonce = 'abc';

        $aggregator->add(
            new CompletedWorkUnit(
                new TestClassWorkUnit(0, self::class, []),
                $nonce . serialize((object) ['codeCoverage' => null, 'events' => new EventCollection, 'passedTests' => new PassedTests]),
                $nonce,
                false,
            ),
        );
    }

    public function testReportsACrashedUnitAsAWarning(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter->expects($this->once())->method('childProcessErrored');
        $emitter->expects($this->once())
            ->method('testRunnerTriggeredPhpunitWarning')
            ->with($this->stringContains('ended unexpectedly'));

        $this->aggregator($emitter)->add(
            new CompletedWorkUnit(new TestClassWorkUnit(0, self::class, []), '', null, true),
        );
    }

    public function testRejectsAResultWhoseNonceDoesNotMatch(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter->expects($this->once())->method('childProcessErrored');
        $emitter->expects($this->once())
            ->method('testRunnerTriggeredPhpunitWarning')
            ->with($this->stringContains('tampered with'));

        $this->aggregator($emitter)->add(
            new CompletedWorkUnit(new TestClassWorkUnit(0, self::class, []), 'expected-nonce' . serialize((object) []), 'actual-nonce', false),
        );
    }

    public function testRejectsAMalformedResult(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter->expects($this->once())->method('childProcessErrored');
        $emitter->expects($this->once())
            ->method('testRunnerTriggeredPhpunitWarning')
            ->with($this->stringContains('ended unexpectedly'));

        $nonce = 'abc';

        $this->aggregator($emitter)->add(
            new CompletedWorkUnit(new TestClassWorkUnit(0, self::class, []), $nonce . 'not-a-serialized-envelope', $nonce, false),
        );
    }

    public function testForwardsBufferedUnitsInSuiteOrderRegardlessOfCompletionOrder(): void
    {
        $messages = [];

        $emitter = $this->createMock(Emitter::class);

        $emitter->method('testRunnerTriggeredPhpunitWarning')->willReturnCallback(
            static function (string $message) use (&$messages): void
            {
                $messages[] = $message;
            },
        );

        $aggregator = $this->aggregator($emitter);

        // The unit at index 1 finishes first; it must not be forwarded until
        // the unit at index 0, which precedes it in suite order, has been
        // forwarded.
        $aggregator->add(new CompletedWorkUnit(new TestClassWorkUnit(1, WorkerSecondTest::class, []), '', null, true));

        $this->assertSame([], $messages);

        $aggregator->add(new CompletedWorkUnit(new TestClassWorkUnit(0, WorkerFirstTest::class, []), '', null, true));

        $this->assertCount(2, $messages);
        $this->assertStringContainsString(WorkerFirstTest::class, $messages[0]);
        $this->assertStringContainsString(WorkerSecondTest::class, $messages[1]);
    }

    public function testRunsInProcessUnitsAtTheirIndexInGlobalSuiteOrder(): void
    {
        $order = [];

        $emitter = $this->createMock(Emitter::class);

        $emitter->method('testRunnerTriggeredPhpunitWarning')->willReturnCallback(
            static function (string $message) use (&$order): void
            {
                $order[] = $message;
            },
        );

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
        $aggregator->add(new CompletedWorkUnit(new TestClassWorkUnit(2, WorkerSecondTest::class, []), '', null, true));

        $this->assertSame([], $order);

        // Once index 0 arrives, index 0 is forwarded, then the in-process unit
        // at index 1 is run in place, then index 2 is released — global order.
        $aggregator->add(new CompletedWorkUnit(new TestClassWorkUnit(0, WorkerFirstTest::class, []), '', null, true));

        $this->assertCount(3, $order);
        $this->assertStringContainsString(WorkerFirstTest::class, $order[0]);
        $this->assertSame('in-process', $order[1]);
        $this->assertStringContainsString(WorkerSecondTest::class, $order[2]);
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

        $emitter->method('testRunnerTriggeredPhpunitWarning')->willReturnCallback(
            static function (string $message) use (&$forwarded): void
            {
                $forwarded[] = $message;
            },
        );

        $aggregator = $this->aggregatorObservedThrough($forwarded, $emitter);

        // The unit at index 1 streams events while the unit at index 0 is
        // still running; they must be held back.
        $aggregator->addStreamedEvents(1, $this->events('first test of unit 1'));

        $this->assertSame([], $forwarded);

        // The unit at index 0 finishes (as a crash, whose warning marks its
        // place in the forwarded sequence); the buffered events of the unit at
        // index 1 follow it immediately, and events the unit streams from now
        // on are forwarded live.
        $aggregator->add(new CompletedWorkUnit(new TestClassWorkUnit(0, WorkerFirstTest::class, []), '', null, true));

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

        $emitter->method('testRunnerTriggeredPhpunitWarning')->willReturnCallback(
            static function (string $message) use (&$forwarded): void
            {
                $forwarded[] = $message;
            },
        );

        $aggregator = $this->aggregatorObservedThrough($forwarded, $emitter);

        // The unit at index 1 streams the events of a test that completed and
        // then crashes, all while the unit at index 0 is still running.
        $aggregator->addStreamedEvents(1, $this->events('completed test of unit 1'));
        $aggregator->add(new CompletedWorkUnit(new TestClassWorkUnit(1, WorkerSecondTest::class, []), '', null, true));

        $this->assertSame([], $forwarded);

        $aggregator->add(new CompletedWorkUnit(new TestClassWorkUnit(0, WorkerFirstTest::class, []), '', null, true));

        $this->assertCount(3, $forwarded);
        $this->assertStringContainsString(WorkerFirstTest::class, $forwarded[0]);
        $this->assertSame('completed test of unit 1', $forwarded[1]);
        $this->assertStringContainsString(WorkerSecondTest::class, $forwarded[2]);
    }

    private function aggregator(Emitter $emitter): ResultAggregator
    {
        return new ResultAggregator(
            new Facade,
            $emitter,
            new PassedTests,
            new CodeCoverage,
        );
    }

    /**
     * An aggregator whose forwarded events are observable: the message of
     * every forwarded test runner warning event is appended to $forwarded.
     * Forwarding a crashed unit records its warning message through the
     * emitter into the same array, so the order of events and crash reports
     * relative to each other is observable, too.
     *
     * @param list<string> $forwarded
     */
    private function aggregatorObservedThrough(array &$forwarded, Emitter $emitter): ResultAggregator
    {
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
        );
    }

    private function events(string $message): EventCollection
    {
        $events = new EventCollection;

        $events->add(
            new WarningTriggered(
                new Telemetry\Info(
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
                ),
                $message,
            ),
        );

        return $events;
    }
}
