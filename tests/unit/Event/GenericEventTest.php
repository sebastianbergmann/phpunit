<?php

declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event;

use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\GenericEvent
 */
final class GenericEventTest extends TestCase
{
    public function testConstructorSetType(): void
    {
        $type = new NamedType('foo');

        $event = new GenericEvent($type);

        $this->assertSame($type, $event->type());
    }
}
