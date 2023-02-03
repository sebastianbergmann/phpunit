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
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\DummyEvent;
use PHPUnit\TestFixture\DummySecondEvent;
use PHPUnit\TestFixture\DummySecondSubscriber;
use PHPUnit\TestFixture\DummySubscriber;
use PHPUnit\TestFixture\SpyingDummyDoubleSubscriber;
use PHPUnit\TestFixture\SpyingDummySubscriber;
use RuntimeException;

#[CoversClass(DirectDispatcher::class)]
final class DirectDispatcherTest extends TestCase
{
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

    public function testDispatchDispatchesEventToKnownSubscribers(): void
    {
        $event = new DummyEvent;

        $typeMap = new TypeMap;

        $typeMap->addMapping(DummySubscriber::class, DummyEvent::class);

        $subscriber = new SpyingDummySubscriber;

        $dispatcher = new DirectDispatcher($typeMap);

        $dispatcher->registerSubscriber($subscriber);

        $dispatcher->dispatch($event);

        $this->assertContains($event, $subscriber->events());
    }

    public function testRegisterDispatcherThatImplementsMultipleInterfaces(): void
    {
        $event1 = new DummyEvent;
        $event2 = new DummySecondEvent;

        $typeMap = new TypeMap;

        $typeMap->addMapping(DummySubscriber::class, DummyEvent::class);
        $typeMap->addMapping(DummySecondSubscriber::class, DummySecondEvent::class);

        $subscriber = new SpyingDummyDoubleSubscriber;

        $dispatcher = new DirectDispatcher($typeMap);

        $dispatcher->registerSubscriber($subscriber);

        $dispatcher->dispatch($event1);
        $dispatcher->dispatch($event2);

        $this->assertCount(2, $subscriber->events());
        $this->assertContains($event1, $subscriber->events());
        $this->assertContains($event2, $subscriber->events());
    }
}
