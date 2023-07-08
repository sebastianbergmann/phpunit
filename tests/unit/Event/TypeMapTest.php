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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\AnotherDummySubscriber;
use PHPUnit\TestFixture\DummyEvent;
use PHPUnit\TestFixture\DummySubscriber;
use PHPUnit\TestFixture\MockObject\AnInterface;
use PHPUnit\TestFixture\MockObject\FinalClass;

#[CoversClass(TypeMap::class)]
#[Small]
final class TypeMapTest extends TestCase
{
    public function testMapsKnownSubscriberInterfaceToEventClass(): void
    {
        $subscriber     = $this->createStub(DummySubscriber::class);
        $subscriberType = DummySubscriber::class;
        $event          = new DummyEvent;
        $eventType      = DummyEvent::class;

        $map = new TypeMap;

        $this->assertFalse($map->isKnownSubscriberType($subscriber));
        $this->assertFalse($map->isKnownEventType($event));

        $map->addMapping($subscriberType, $eventType);

        $this->assertTrue($map->isKnownSubscriberType($subscriber));
        $this->assertTrue($map->isKnownEventType($event));

        $this->assertSame($eventType, $map->map($subscriber));
    }

    public function testCannotMapUnknownSubscriberInterface(): void
    {
        $subscriber = $this->createStub(DummySubscriber::class);

        $map = new TypeMap;

        $this->expectException(MapError::class);

        $map->map($subscriber);
    }

    public function testCannotAddMappingFromUnknownSubscriberInterface(): void
    {
        $subscriberType = 'DoesNotExist';
        $eventType      = DummyEvent::class;

        $map = new TypeMap;

        $this->expectException(UnknownSubscriberException::class);

        $map->addMapping($subscriberType, $eventType);
    }

    public function testCannotAddMappingFromInvalidSubscriberInterface(): void
    {
        $subscriberType = AnInterface::class;
        $eventType      = DummyEvent::class;

        $map = new TypeMap;

        $this->expectException(InvalidSubscriberException::class);

        $map->addMapping($subscriberType, $eventType);
    }

    public function testCannotAddMappingToUnknownEventClass(): void
    {
        $subscriberType = DummySubscriber::class;
        $eventType      = 'DoesNotExist';

        $map = new TypeMap;

        $this->expectException(UnknownEventException::class);

        $map->addMapping($subscriberType, $eventType);
    }

    public function testCannotAddMappingToInvalidEventClass(): void
    {
        $subscriberType = DummySubscriber::class;
        $eventType      = FinalClass::class;

        $map = new TypeMap;

        $this->expectException(InvalidEventException::class);

        $map->addMapping($subscriberType, $eventType);
    }

    public function testSubscriberInterfaceCanOnlyBeRegisteredOnce(): void
    {
        $subscriberType = DummySubscriber::class;
        $eventType      = DummyEvent::class;

        $map = new TypeMap;

        $map->addMapping($subscriberType, $eventType);

        $this->expectException(SubscriberTypeAlreadyRegisteredException::class);

        $map->addMapping($subscriberType, $eventType);
    }

    public function testEventClassCanOnlyBeRegisteredOnce(): void
    {
        $subscriberType        = DummySubscriber::class;
        $anotherSubscriberType = AnotherDummySubscriber::class;
        $eventType             = DummyEvent::class;

        $map = new TypeMap;

        $map->addMapping($subscriberType, $eventType);

        $this->expectException(EventAlreadyAssignedException::class);

        $map->addMapping($anotherSubscriberType, $eventType);
    }
}
