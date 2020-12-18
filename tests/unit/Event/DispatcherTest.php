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
use NamedType;
use PHPUnit\Framework\TestCase;
use SpyingSubscriber;

/**
 * @covers \PHPUnit\Event\Dispatcher
 */
final class DispatcherTest extends TestCase
{
    public function testDispatchDispatchesToRegisteredSubscribersForEventType(): void
    {
        $firstSubscriber = new SpyingSubscriber(new Types(
            new NamedType('foo'),
            new NamedType('bar')
        ));

        $secondSubscriber = new SpyingSubscriber(new Types(
            new NamedType('bar'),
            new NamedType('baz')
        ));

        $thirdSubscriber = new SpyingSubscriber(new Types(new NamedType('qux')));

        $event = new DummyEvent(new NamedType('bar'));

        $dispatcher = new Dispatcher();

        $dispatcher->register(
            $firstSubscriber,
            $secondSubscriber,
            $thirdSubscriber
        );

        $dispatcher->dispatch($event);

        $this->assertContains($event, $firstSubscriber->events());
        $this->assertContains($event, $secondSubscriber->events());
        $this->assertNotContains($event, $thirdSubscriber->events());
    }
}
