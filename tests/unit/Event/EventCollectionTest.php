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

#[CoversClass(EventCollection::class)]
#[CoversClass(EventCollectionIterator::class)]
#[Small]
final class EventCollectionTest extends TestCase
{
    public function testIsInitiallyEmpty(): void
    {
        $events = new EventCollection;

        $this->assertEmpty($events);
        $this->assertTrue($events->isEmpty());
        $this->assertFalse($events->isNotEmpty());
        $this->assertSame([], $events->asArray());
    }

    public function testCollectsEventObjects(): void
    {
        $event  = $this->createStub(Event::class);
        $events = new EventCollection;

        $events->add($event);

        $this->assertNotEmpty($events);
        $this->assertTrue($events->isNotEmpty());
        $this->assertFalse($events->isEmpty());
        $this->assertSame([$event], $events->asArray());
    }

    public function testCanBeIterated(): void
    {
        $event  = $this->createStub(Event::class);
        $events = new EventCollection;

        $events->add($event);

        foreach ($events as $index => $_event) {
            $this->assertSame(0, $index);
            $this->assertSame($event, $_event);
        }
    }
}
