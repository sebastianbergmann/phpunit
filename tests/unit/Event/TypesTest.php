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

use function iterator_to_array;
use NamedType;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\Types
 */
final class TypesTest extends TestCase
{
    public function testDefaults(): void
    {
        $types = new Types();

        $this->assertIsIterable($types);
        $this->assertEquals([], iterator_to_array($types));
    }

    public function testConstructorSetsTypes(): void
    {
        $composedTypes = [
            new NamedType('foo'),
            new NamedType('bar'),
            new NamedType('baz'),
        ];

        $types = new Types(...$composedTypes);

        $this->assertIsIterable($types);
        $this->assertEquals($composedTypes, iterator_to_array($types));
    }

    public function testContainsReturnsFalseWhenEqualTypeHasNotBeenComposed(): void
    {
        $composedTypes = [
            new NamedType('foo'),
            new NamedType('bar'),
            new NamedType('baz'),
        ];

        $type = new NamedType('qux');

        $types = new Types(...$composedTypes);

        $this->assertFalse($types->contains($type));
    }

    public function testContainsReturnsTrueWhenEqualTypeHasBeenComposed(): void
    {
        $composedTypes = [
            new NamedType('foo'),
            new NamedType('bar'),
            new NamedType('baz'),
        ];

        $type = new NamedType('bar');

        $types = new Types(...$composedTypes);

        $this->assertTrue($types->contains($type));
    }
}
