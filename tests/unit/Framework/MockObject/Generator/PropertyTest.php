<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Generator;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Property::class)]
#[Group('test-doubles')]
#[Small]
final class PropertyTest extends TestCase
{
    public function testHasName(): void
    {
        $name = 'property-name';

        $property = new Property($name, 'property-type', false, false);

        $this->assertSame($name, $property->name());
    }

    public function testHasType(): void
    {
        $type = 'property-type';

        $property = new Property('property-type', $type, false, false);

        $this->assertSame($type, $property->type());
    }

    public function testMayHaveGetHook(): void
    {
        $property = new Property('property-name', 'property-type', true, false);

        $this->assertTrue($property->hasGetHook());
    }

    public function testMayNotHaveGetHook(): void
    {
        $property = new Property('property-name', 'property-type', false, false);

        $this->assertFalse($property->hasGetHook());
    }

    public function testMayHaveSetHook(): void
    {
        $property = new Property('property-name', 'property-type', false, true);

        $this->assertTrue($property->hasSetHook());
    }

    public function testMayNotHaveSetHook(): void
    {
        $property = new Property('property-name', 'property-type', false, false);

        $this->assertFalse($property->hasSetHook());
    }
}
