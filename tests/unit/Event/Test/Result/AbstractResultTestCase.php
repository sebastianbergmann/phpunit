<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Test\Result;

use PHPUnit\Event\Test\Result;
use PHPUnit\Framework\TestCase;

abstract class AbstractResultTestCase extends TestCase
{
    final public function testAsStringReturnsStringRepresentation(): void
    {
        $result = static::result();

        $this->assertSame(static::asString(), $result->asString());
    }

    final public function testIsReturnsFalseWhenStringRepresentationIsDifferent(): void
    {
        $result = static::result();

        $other = new Named('foo');

        $this->assertFalse($result->is($other));
    }

    final public function testIsReturnsTrueWhenStringRepresentationIsSame(): void
    {
        $type = static::result();

        $other = new Named(static::asString());

        $this->assertTrue($type->is($other));
    }

    abstract protected function asString(): string;

    abstract protected function result(): Result;
}
