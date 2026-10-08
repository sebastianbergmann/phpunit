<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

use PHPUnit\Event\Tracer\Tracer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\DummySubscriber;

#[CoversClass(DeferringDispatcher::class)]
#[Small]
#[Group('event-system')]
#[Group('event-system/dispatcher')]
final class DeferringDispatcherTest extends TestCase
{
    public function testCollectsEventsUntilFlush(): void
    {
        $subscribableDispatcher = $this->createMock(SubscribableDispatcher::class);

        $subscribableDispatcher
            ->expects($this->never())
            ->method('dispatch')
            ->seal();

        $deferringDispatcher = new DeferringDispatcher($subscribableDispatcher, $this->createStub(SubscribableDispatcher::class));

        $deferringDispatcher->dispatch($this->createStub(Event::class));
    }

    public function testFlushesCollectedEvents(): void
    {
        $event = $this->createStub(Event::class);

        $subscribableDispatcher = $this->createMock(SubscribableDispatcher::class);

        $subscribableDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->identicalTo($event))
            ->seal();

        $deferringDispatcher = new DeferringDispatcher($subscribableDispatcher, $this->createStub(SubscribableDispatcher::class));

        $deferringDispatcher->dispatch($event);

        $deferringDispatcher->flush();
    }

    public function testCollectsEventsWhileCollectionIsActive(): void
    {
        $event = $this->createStub(Event::class);

        $subscribableDispatcher = $this->createMock(SubscribableDispatcher::class);

        $subscribableDispatcher
            ->expects($this->never())
            ->method('dispatch')
            ->seal();

        $deferringDispatcher = new DeferringDispatcher($subscribableDispatcher, $this->createStub(SubscribableDispatcher::class));

        $deferringDispatcher->flush();

        $deferringDispatcher->startCollectingEvents();

        $deferringDispatcher->dispatch($event);

        $events = $deferringDispatcher->stopCollectingEvents();

        $this->assertCount(1, $events);
        $this->assertSame($event, $events->asArray()[0]);
    }

    public function testCollectionTakesPrecedenceOverRecording(): void
    {
        $event = $this->createStub(Event::class);

        $subscribableDispatcher = $this->createMock(SubscribableDispatcher::class);

        $subscribableDispatcher
            ->expects($this->never())
            ->method('dispatch')
            ->seal();

        $deferringDispatcher = new DeferringDispatcher($subscribableDispatcher, $this->createStub(SubscribableDispatcher::class));

        $deferringDispatcher->startCollectingEvents();

        $deferringDispatcher->dispatch($event);

        $events = $deferringDispatcher->stopCollectingEvents();

        $this->assertCount(1, $events);
        $this->assertSame($event, $events->asArray()[0]);

        $deferringDispatcher->flush();
    }

    public function testDispatchesDirectlyAfterCollectionHasStopped(): void
    {
        $event = $this->createStub(Event::class);

        $subscribableDispatcher = $this->createMock(SubscribableDispatcher::class);

        $subscribableDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->identicalTo($event))
            ->seal();

        $deferringDispatcher = new DeferringDispatcher($subscribableDispatcher, $this->createStub(SubscribableDispatcher::class));

        $deferringDispatcher->flush();

        $deferringDispatcher->startCollectingEvents();
        $deferringDispatcher->stopCollectingEvents();

        $deferringDispatcher->dispatch($event);
    }

    public function testDispatchesEventsToSubscribersForCollectedEventsWhileCollectionIsActive(): void
    {
        $event = $this->createStub(Event::class);

        $subscribableDispatcher = $this->createMock(SubscribableDispatcher::class);

        $subscribableDispatcher
            ->expects($this->never())
            ->method('dispatch')
            ->seal();

        $collectedEventsDispatcher = $this->createMock(SubscribableDispatcher::class);

        $collectedEventsDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->identicalTo($event))
            ->seal();

        $deferringDispatcher = new DeferringDispatcher($subscribableDispatcher, $collectedEventsDispatcher);

        $deferringDispatcher->flush();

        $deferringDispatcher->startCollectingEvents();

        $deferringDispatcher->dispatch($event);

        $events = $deferringDispatcher->stopCollectingEvents();

        $this->assertCount(1, $events);
        $this->assertSame($event, $events->asArray()[0]);
    }

    public function testDoesNotDispatchEventsToSubscribersForCollectedEventsWhileCollectionIsNotActive(): void
    {
        $event = $this->createStub(Event::class);

        $collectedEventsDispatcher = $this->createMock(SubscribableDispatcher::class);

        $collectedEventsDispatcher
            ->expects($this->never())
            ->method('dispatch')
            ->seal();

        $deferringDispatcher = new DeferringDispatcher($this->createStub(SubscribableDispatcher::class), $collectedEventsDispatcher);

        $deferringDispatcher->dispatch($event);

        $deferringDispatcher->flush();

        $deferringDispatcher->dispatch($event);
    }

    public function testCannotStartCollectingEventsWhileEventsAreAlreadyBeingCollected(): void
    {
        $subscribableDispatcher = $this->createMock(SubscribableDispatcher::class);

        $subscribableDispatcher
            ->expects($this->never())
            ->method('dispatch')
            ->seal();

        $deferringDispatcher = new DeferringDispatcher($subscribableDispatcher, $this->createStub(SubscribableDispatcher::class));

        $deferringDispatcher->startCollectingEvents();

        $this->expectException(EventsAreAlreadyBeingCollectedException::class);

        $deferringDispatcher->startCollectingEvents();
    }

    public function testCannotStopCollectingEventsWhileNoEventsAreBeingCollected(): void
    {
        $subscribableDispatcher = $this->createMock(SubscribableDispatcher::class);

        $subscribableDispatcher
            ->expects($this->never())
            ->method('dispatch')
            ->seal();

        $deferringDispatcher = new DeferringDispatcher($subscribableDispatcher, $this->createStub(SubscribableDispatcher::class));

        $this->expectException(EventsAreNotBeingCollectedException::class);

        $deferringDispatcher->stopCollectingEvents();
    }

    public function testSubscriberCanBeRegistered(): void
    {
        $subscriber = $this->createMock(DummySubscriber::class);

        $subscribableDispatcher = $this->createMock(SubscribableDispatcher::class);

        $subscribableDispatcher
            ->expects($this->once())
            ->method('registerSubscriber')
            ->with($this->identicalTo($subscriber))
            ->seal();

        $deferringDispatcher = new DeferringDispatcher($subscribableDispatcher, $this->createStub(SubscribableDispatcher::class));

        $deferringDispatcher->registerSubscriber($subscriber);
    }

    public function testSubscriberForCollectedEventsCanBeRegistered(): void
    {
        $subscriber = $this->createStub(DummySubscriber::class);

        $subscribableDispatcher = $this->createMock(SubscribableDispatcher::class);

        $subscribableDispatcher
            ->expects($this->never())
            ->method('registerSubscriber')
            ->seal();

        $collectedEventsDispatcher = $this->createMock(SubscribableDispatcher::class);

        $collectedEventsDispatcher
            ->expects($this->once())
            ->method('registerSubscriber')
            ->with($this->identicalTo($subscriber))
            ->seal();

        $deferringDispatcher = new DeferringDispatcher($subscribableDispatcher, $collectedEventsDispatcher);

        $deferringDispatcher->registerSubscriberForCollectedEvents($subscriber);
    }

    public function testTracerCanBeRegistered(): void
    {
        $tracer = $this->createStub(Tracer::class);

        $subscribableDispatcher = $this->createMock(SubscribableDispatcher::class);

        $subscribableDispatcher
            ->expects($this->once())
            ->method('registerTracer')
            ->with($this->identicalTo($tracer))
            ->seal();

        $deferringDispatcher = new DeferringDispatcher($subscribableDispatcher, $this->createStub(SubscribableDispatcher::class));

        $deferringDispatcher->registerTracer($tracer);
    }
}
