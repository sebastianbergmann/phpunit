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

#[CoversClass(CollectingDispatcher::class)]
#[Small]
final class CollectingDispatcherTest extends TestCase
{
    public function testHasNoCollectedEventsWhenFlushedImmediatelyAfterCreation(): void
    {
        $typeMap = new TypeMap;
        $typeMap->addMapping(Test\DeprecationTriggeredSubscriber::class, Test\DeprecationTriggered::class);

        $dispatcher = new CollectingDispatcher(new DirectDispatcher($typeMap));

        $this->assertEmpty($dispatcher->flush());
    }

    public function testCollectsDispatchedEventsUntilFlushed(): void
    {
        $typeMap = new TypeMap;
        $typeMap->addMapping(Test\DeprecationTriggeredSubscriber::class, Test\DeprecationTriggered::class);

        $dispatcher = new CollectingDispatcher(new DirectDispatcher($typeMap));
        $event      = $this->createStub(Event::class);

        $dispatcher->dispatch($event);

        $this->assertSame([$event], $dispatcher->flush()->asArray());
    }
}
