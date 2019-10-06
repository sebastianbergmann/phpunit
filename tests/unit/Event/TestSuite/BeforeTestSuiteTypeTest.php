<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\TestSuite;

use PHPUnit\Event\NamedType;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Event\Test\BeforeTestSuiteType
 */
final class BeforeTestSuiteTypeTest extends TestCase
{
    public function testAsStringReturnsBeforeTestSuite(): void
    {
        $type = new BeforeTestSuiteType();

        $this->assertSame('before-test-suite', $type->asString());
    }

    public function testIsReturnsFalseWhenTypeIsDifferentType(): void
    {
        $type = new BeforeTestSuiteType();

        $other = new NamedType('foo');

        $this->assertFalse($type->is($other));
    }

    public function testIsReturnsTrueWhenTypeIsEqualType(): void
    {
        $type = new BeforeTestSuiteType();

        $other = new NamedType('before-test-suite');

        $this->assertTrue($type->is($other));
    }
}
