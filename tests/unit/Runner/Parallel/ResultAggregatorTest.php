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

use function serialize;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade;
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

    private function aggregator(Emitter $emitter): ResultAggregator
    {
        return new ResultAggregator(
            new Facade,
            $emitter,
            new PassedTests,
            new CodeCoverage,
        );
    }
}
