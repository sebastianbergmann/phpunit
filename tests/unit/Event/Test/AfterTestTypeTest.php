<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Test;

use PHPUnit\Event\NamedType;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\Test\AfterTestType
 */
final class AfterTestTypeTest extends TestCase
{
    public function testAsStringReturnsAfterTest(): void
    {
        $type = new AfterTestType();

        $this->assertSame('after-test', $type->asString());
    }

    public function testIsReturnsFalseWhenTypeIsDifferentType(): void
    {
        $type = new AfterTestType();

        $other = new NamedType('foo');

        $this->assertFalse($type->is($other));
    }

    public function testIsReturnsTrueWhenTypeIsEqualType(): void
    {
        $type = new AfterTestType();

        $other = new NamedType('after-test');

        $this->assertTrue($type->is($other));
    }
}
