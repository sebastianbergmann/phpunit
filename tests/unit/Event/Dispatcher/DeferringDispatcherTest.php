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
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\DummySubscriber;

#[CoversClass(DeferringDispatcher::class)]
#[Small]
final class DeferringDispatcherTest extends TestCase
{
    public function testCollectsEventsUntilFlush(): void
    {
        $subscribableDispatcher = $this->createMock(SubscribableDispatcher::class);

        $subscribableDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $deferringDispatcher = new DeferringDispatcher($subscribableDispatcher);

        $deferringDispatcher->dispatch($this->createStub(Event::class));
    }

    public function testFlushesCollectedEvents(): void
    {
        $event = $this->createStub(Event::class);

        $subscribableDispatcher = $this->createMock(SubscribableDispatcher::class);

        $subscribableDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->identicalTo($event));

        $deferringDispatcher = new DeferringDispatcher($subscribableDispatcher);

        $deferringDispatcher->dispatch($event);

        $deferringDispatcher->flush();
    }

    public function testSubscriberCanBeRegistered(): void
    {
        $subscriber = $this->createMock(DummySubscriber::class);

        $subscribableDispatcher = $this->createMock(SubscribableDispatcher::class);

        $subscribableDispatcher
            ->expects($this->once())
            ->method('registerSubscriber')
            ->with($this->identicalTo($subscriber));

        $deferringDispatcher = new DeferringDispatcher($subscribableDispatcher);

        $deferringDispatcher->registerSubscriber($subscriber);
    }

    public function testTracerCanBeRegistered(): void
    {
        $tracer = $this->createStub(Tracer::class);

        $subscribableDispatcher = $this->createMock(SubscribableDispatcher::class);

        $subscribableDispatcher
            ->expects($this->once())
            ->method('registerTracer')
            ->with($this->identicalTo($tracer));

        $deferringDispatcher = new DeferringDispatcher($subscribableDispatcher);

        $deferringDispatcher->registerTracer($tracer);
    }
}
