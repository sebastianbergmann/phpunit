<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use function hrtime;
use function serialize;
use PHPUnit\Event\Emitter;
use PHPUnit\Event\Event;
use PHPUnit\Event\EventCollection;
use PHPUnit\Event\Facade;
use PHPUnit\Event\Telemetry\CpuTime;
use PHPUnit\Event\Telemetry\Duration;
use PHPUnit\Event\Telemetry\GarbageCollectorStatus;
use PHPUnit\Event\Telemetry\HRTime;
use PHPUnit\Event\Telemetry\Info;
use PHPUnit\Event\Telemetry\MemoryUsage;
use PHPUnit\Event\Telemetry\Snapshot;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestRunner\ChildProcessResultProcessor;
use PHPUnit\Framework\TestStatus\TestStatus;
use PHPUnit\Runner\CodeCoverage;
use PHPUnit\TestFixture\Success;
use PHPUnit\TestRunner\TestResult\PassedTests;

#[CoversClass(ChildProcessResultProcessor::class)]
#[Small]
final class ChildProcessResultProcessorTest extends TestCase
{
    #[TestDox('Emits Test\Errored event when standard output is not empty')]
    public function testEmitsErrorEventWhenStderrIsNotEmpty(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('testErrored');

        $emitter
            ->expects($this->once())
            ->method('testFinished')
            ->seal();

        $this->processor($emitter)->process(new Success('testOne'), '', 'error');
    }

    #[TestDox('Does not forward the Test\PreparationStarted event of the child process')]
    public function testDoesNotForwardPreparationStartedEventOfChildProcess(): void
    {
        $test        = new Success('testOne');
        $childResult = (object) [
            'testResult'    => null,
            'status'        => TestStatus::success(),
            'numAssertions' => 0,
            'events'        => $this->events(
                new PreparationStarted($this->telemetryInfo(), $test->valueObjectForEvents()),
                new Prepared($this->telemetryInfo(), $test->valueObjectForEvents()),
                new Finished($this->telemetryInfo(), $test->valueObjectForEvents(), 0),
            ),
            'passedTests' => new PassedTests,
        ];

        $facade = new Facade;

        $facade->startCollectingEvents();

        $this->processor($this->createStub(Emitter::class), $facade)->process($test, serialize($childResult), '');

        $forwardedEvents = $facade->stopCollectingEvents()->asArray();

        $this->assertCount(2, $forwardedEvents);
        $this->assertInstanceOf(Prepared::class, $forwardedEvents[0]);
        $this->assertInstanceOf(Finished::class, $forwardedEvents[1]);
    }

    #[TestDox('Emits Test\Errored event when process result cannot be unserialized')]
    public function testEmitsErrorEventWhenProcessResultCannotBeUnserialized(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('childProcessErrored');

        $emitter
            ->expects($this->once())
            ->method('testErrored');

        $emitter
            ->expects($this->once())
            ->method('testFinished')
            ->seal();

        $this->processor($emitter)->process(new Success('testOne'), '', '');
    }

    #[TestDox('Emits Test\Errored event when process result nonce is shorter than the expected nonce')]
    public function testEmitsErrorEventWhenProcessResultIsShorterThanExpectedNonce(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('childProcessErrored');

        $emitter
            ->expects($this->once())
            ->method('testErrored');

        $emitter
            ->expects($this->once())
            ->method('testFinished')
            ->seal();

        $this->processor($emitter)->process(
            new Success('testOne'),
            'too-short',
            '',
            'abcdef0123456789abcdef0123456789',
        );
    }

    #[TestDox('Emits Test\Errored event when process result nonce does not match the expected nonce')]
    public function testEmitsErrorEventWhenProcessResultNonceDoesNotMatchExpectedNonce(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('childProcessErrored');

        $emitter
            ->expects($this->once())
            ->method('testErrored');

        $emitter
            ->expects($this->once())
            ->method('testFinished')
            ->seal();

        $this->processor($emitter)->process(
            new Success('testOne'),
            '00000000000000000000000000000000payload',
            '',
            'abcdef0123456789abcdef0123456789',
        );
    }

    #[TestDox('Emits Test\Errored event when process result contains only the expected nonce and no serialized data')]
    public function testEmitsErrorEventWhenProcessResultContainsOnlyExpectedNonceAndNoSerializedData(): void
    {
        $emitter = $this->createMock(Emitter::class);

        $emitter
            ->expects($this->once())
            ->method('childProcessErrored');

        $emitter
            ->expects($this->once())
            ->method('testErrored');

        $emitter
            ->expects($this->once())
            ->method('testFinished')
            ->seal();

        $this->processor($emitter)->process(
            new Success('testOne'),
            'abcdef0123456789abcdef0123456789',
            '',
            'abcdef0123456789abcdef0123456789',
        );
    }

    private function processor(Emitter $emitter, Facade $facade = new Facade): ChildProcessResultProcessor
    {
        return new ChildProcessResultProcessor(
            $facade,
            $emitter,
            new PassedTests,
            new CodeCoverage($this->createStub(Emitter::class)),
        );
    }

    private function events(Event ...$events): EventCollection
    {
        $collection = new EventCollection;

        $collection->add(...$events);

        return $collection;
    }

    private function telemetryInfo(): Info
    {
        return new Info(
            new Snapshot(
                HRTime::fromSecondsAndNanoseconds(...hrtime(false)),
                MemoryUsage::fromBytes(1000),
                MemoryUsage::fromBytes(2000),
                new GarbageCollectorStatus(0, 0, 0, 0, 0.0, 0.0, 0.0, 0.0, false, false, false, 0),
                CpuTime::fromSecondsAndNanoseconds(0, 0),
                CpuTime::fromSecondsAndNanoseconds(0, 0),
                CpuTime::fromSecondsAndNanoseconds(0, 0),
            ),
            Duration::fromSecondsAndNanoseconds(123, 456),
            MemoryUsage::fromBytes(2000),
            Duration::fromSecondsAndNanoseconds(234, 567),
            MemoryUsage::fromBytes(3000),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            CpuTime::fromSecondsAndNanoseconds(0, 0),
            12345,
        );
    }
}
