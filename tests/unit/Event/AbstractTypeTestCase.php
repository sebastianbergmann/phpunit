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

use PHPUnit\Framework\TestCase;

abstract class AbstractTypeTestCase extends TestCase
{
    final public function testAsStringReturnsStringRepresentation(): void
    {
        $type = static::type();

        $this->assertSame(static::asString(), $type->asString());
    }

    final public function testIsReturnsFalseWhenStringRepresentationIsDifferent(): void
    {
        $type = static::type();

        $other = new GenericType('foo');

        $this->assertFalse($type->is($other));
    }

    final public function testIsReturnsTrueWhenStringRepresentationIsSame(): void
    {
        $type = static::type();

        $other = new GenericType(static::asString());

        $this->assertTrue($type->is($other));
    }

    abstract protected function asString(): string;

    abstract protected function type(): Type;
}
