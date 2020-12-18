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

use PHPUnit\Event\Run\BeforeRun;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\Dispatcher
 */
final class DispatcherTest extends TestCase
{
    public function testDispatcherDoesNotDispatchEventToSubscriberImplementingUnknownSubscriberInterfaces(): void
    {
        $event = new Run\BeforeRun(new Run\Run());

        $subscriber         = new class implements Subscriber {
            private $events = [];

            public function notify(BeforeRun $event): void
            {
                $this->events[] = $event;
            }

            public function events(): array
            {
                return $this->events;
            }
        };

        $dispatcher = new Dispatcher();

        $dispatcher->register($subscriber);

        $dispatcher->dispatch($event);

        $this->assertSame([], $subscriber->events());
    }

    public function testDispatcherDispatchesEventToSubscriberImplementingKnownSubscriberInterfaces(): void
    {
        $event = new Run\BeforeRun(new Run\Run());

        $subscriber         = new class implements Run\BeforeRunSubscriber {
            private $events = [];

            public function notify(BeforeRun $event): void
            {
                $this->events[] = $event;
            }

            public function events(): array
            {
                return $this->events;
            }
        };

        $dispatcher = new Dispatcher();

        $dispatcher->register($subscriber);

        $dispatcher->dispatch($event);

        $expected = [
            $event,
        ];

        $this->assertSame($expected, $subscriber->events());
    }
}
