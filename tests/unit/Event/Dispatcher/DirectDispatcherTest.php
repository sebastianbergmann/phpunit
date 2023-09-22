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
use PHPUnit\TestFixture\DummyEvent;
use PHPUnit\TestFixture\DummySubscriber;
use RuntimeException;

#[CoversClass(DirectDispatcher::class)]
#[Small]
final class DirectDispatcherTest extends TestCase
{
    public function testDispatchesEventToKnownSubscribers(): void
    {
        $event   = new DummyEvent;
        $typeMap = $this->typeMap();

        $dispatcher = new DirectDispatcher($typeMap);

        $subscriber = $this->createMock(DummySubscriber::class);

        $subscriber
            ->expects($this->once())
            ->method('notify')
            ->with($this->identicalTo($event));

        $dispatcher->registerSubscriber($subscriber);

        $dispatcher->dispatch($event);
    }

    public function testDispatchesEventToTracers(): void
    {
        $event   = new DummyEvent;
        $typeMap = $this->typeMap();

        $dispatcher = new DirectDispatcher($typeMap);

        $tracer = $this->createMock(Tracer::class);

        $tracer
            ->expects($this->once())
            ->method('trace')
            ->with($this->identicalTo($event));

        $dispatcher->registerTracer($tracer);

        $dispatcher->dispatch($event);
    }

    public function testRegisterRejectsUnknownSubscriber(): void
    {
        $subscriber = $this->createStub(Subscriber::class);

        $dispatcher = new DirectDispatcher(new TypeMap);

        $this->expectException(RuntimeException::class);

        $dispatcher->registerSubscriber($subscriber);
    }

    public function testDispatchRejectsUnknownEventType(): void
    {
        $event = new DummyEvent;

        $dispatcher = new DirectDispatcher(new TypeMap);

        $this->expectException(RuntimeException::class);

        $dispatcher->dispatch($event);
    }

    private function typeMap(): TypeMap
    {
        $typeMap = new TypeMap;

        $typeMap->addMapping(DummySubscriber::class, DummyEvent::class);

        return $typeMap;
    }
}
