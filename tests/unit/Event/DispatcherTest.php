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

use DummyEvent;
use DummySubscriber;
use NullSubscriber;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SpyingDummySubscriber;

/**
 * @covers \PHPUnit\Event\Dispatcher
 */
final class DispatcherTest extends TestCase
{
    public function testRegisterRejectsUnknownSubscriber(): void
    {
        $subscriber = new NullSubscriber();

        $dispatcher = new Dispatcher(new TypeMap());

        self::expectException(RuntimeException::class);

        $dispatcher->register($subscriber);
    }

    public function testDispatchRejectsUnknownEventType(): void
    {
        $event = new DummyEvent();

        $dispatcher = new Dispatcher(new TypeMap());

        self::expectException(RuntimeException::class);

        $dispatcher->dispatch($event);
    }

    public function testDispatchDispatchesEventToKnownSubscribers(): void
    {
        $event = new DummyEvent();

        $typeMap = new TypeMap();

        $typeMap->addMapping(DummySubscriber::class, DummyEvent::class);

        $subscriber = new SpyingDummySubscriber();

        $dispatcher = new Dispatcher($typeMap);

        $dispatcher->register($subscriber);

        $dispatcher->dispatch($event);

        $this->assertContains($event, $subscriber->events());
    }
}
