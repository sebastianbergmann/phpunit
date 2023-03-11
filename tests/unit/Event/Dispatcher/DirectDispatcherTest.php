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
use PHPUnit\TestFixture\DummySubscriber;
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
}
