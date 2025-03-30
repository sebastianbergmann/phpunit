<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\TestCase;
use stdClass;

final class FailureTest extends TestCase
{
    public function testAssertArrayEqualsArray(): void
    {
        $this->assertEquals([1], [2], 'message');
    }

    public function testAssertIntegerEqualsInteger(): void
    {
        $this->assertEquals(1, 2, 'message');
    }

    public function testAssertObjectEqualsObject(): void
    {
        $a      = new stdClass;
        $a->foo = 'bar';

        $b      = new stdClass;
        $b->bar = 'foo';

        $this->assertEquals($a, $b, 'message');
    }

    public function testAssertNullEqualsString(): void
    {
        $this->assertEquals(null, 'bar', 'message');
    }

    public function testAssertStringEqualsString(): void
    {
        $this->assertEquals('foo', 'bar', 'message');
    }

    public function testAssertTextEqualsText(): void
    {
        $this->assertEquals("foo\nbar\n", "foo\nbaz\n", 'message');
    }

    public function testAssertStringMatchesFormat(): void
    {
        $this->assertStringMatchesFormat('*%s*', '**', 'message');
    }

    public function testAssertNumericEqualsNumeric(): void
    {
        $this->assertEquals(1, 2, 'message');
    }

    public function testAssertTextSameText(): void
    {
        $this->assertSame('foo', 'bar', 'message');
    }

    public function testAssertObjectSameObject(): void
    {
        $this->assertSame(new stdClass, new stdClass, 'message');
    }

    public function testAssertObjectSameNull(): void
    {
        $this->assertSame(new stdClass, null, 'message');
    }

    public function testAssertFloatSameFloat(): void
    {
        $this->assertSame(1.0, 1.5, 'message');
    }

    // Note that due to the implementation of this assertion it counts as 2 asserts
    public function testAssertStringMatchesFormatFile(): void
    {
        $this->assertStringMatchesFormatFile(__DIR__ . '/expectedFileFormat.txt', '...BAR...');
    }
}
