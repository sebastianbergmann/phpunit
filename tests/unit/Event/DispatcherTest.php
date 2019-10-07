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
use PHPUnit\Framework\TestCase;
use SpyingSubscriber;

/**
 * @covers \PHPUnit\Event\Dispatcher
 */
final class DispatcherTest extends TestCase
{
    public function testDispatchDoesNotDispatchEventToSubscribersNotSubscribedToEventType(): void
    {
        $subscriber = new SpyingSubscriber(new Types(
            new GenericType('bar'),
            new GenericType('baz')
        ));

        $event = new DummyEvent(new GenericType('foo'));

        $dispatcher = new Dispatcher();

        $dispatcher->register($subscriber);

        $dispatcher->dispatch($event);

        $this->assertNotContains($event, $subscriber->events());
    }

    public function testDispatchDispatchesToRegisteredSubscribersForEventType(): void
    {
        $firstSubscriber = new SpyingSubscriber(new Types(
            new GenericType('foo'),
            new GenericType('bar')
        ));

        $secondSubscriber = new SpyingSubscriber(new Types(
            new GenericType('bar'),
            new GenericType('baz')
        ));

        $thirdSubscriber = new SpyingSubscriber(new Types(new GenericType('qux')));

        $event = new DummyEvent(new GenericType('bar'));

        $dispatcher = new Dispatcher();

        $dispatcher->register($firstSubscriber);
        $dispatcher->register($secondSubscriber);
        $dispatcher->register($thirdSubscriber);

        $dispatcher->dispatch($event);

        $this->assertContains($event, $firstSubscriber->events());
        $this->assertContains($event, $secondSubscriber->events());
        $this->assertNotContains($event, $thirdSubscriber->events());
    }
}
