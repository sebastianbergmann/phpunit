<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Extension;

use PHPUnit\Event\CollectingDispatcher;
use PHPUnit\Event\DirectDispatcher;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\DeprecationTriggeredSubscriber;
use PHPUnit\Event\TypeMap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\DummyEvent;
use PHPUnit\TestFixture\DummySubscriber;

#[CoversClass(ChildProcessExtensionFacade::class)]
#[UsesClass(CollectingDispatcher::class)]
#[UsesClass(DirectDispatcher::class)]
#[UsesClass(TypeMap::class)]
#[Small]
final class ChildProcessExtensionFacadeTest extends TestCase
{
    public function testRegistersSubscribersWithTheDispatcher(): void
    {
        $typeMap = new TypeMap;
        $typeMap->addMapping(DeprecationTriggeredSubscriber::class, DeprecationTriggered::class);
        $typeMap->addMapping(DummySubscriber::class, DummyEvent::class);

        $dispatcher = new CollectingDispatcher(new DirectDispatcher($typeMap));
        $event      = new DummyEvent;

        $first = $this->createMock(DummySubscriber::class);

        $first
            ->expects($this->once())
            ->method('notify')
            ->with($this->identicalTo($event))
            ->seal();

        $second = $this->createMock(DummySubscriber::class);

        $second
            ->expects($this->once())
            ->method('notify')
            ->with($this->identicalTo($event))
            ->seal();

        new ChildProcessExtensionFacade($dispatcher)->registerSubscribers($first, $second);

        $dispatcher->dispatch($event);
    }
}
