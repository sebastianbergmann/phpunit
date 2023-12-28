<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\LogEventsText;

use function fopen;
use PHPUnit\Framework\TestCase;
use stdClass;

final class Test extends TestCase
{
    public function testExportNull(): void
    {
        $this->assertNull(null);
    }

    public function testExportBool(): void
    {
        $this->assertTrue(true);
    }

    public function testExportInt(): void
    {
        $this->assertSame(1, 1);
    }

    public function testExportStr(): void
    {
        $this->assertSame('hello, world!', 'hello, world!');
    }

    public function testExportArray(): void
    {
        $arr = [1, 'foo' => 2];
        $this->assertSame($arr, $arr);
    }

    public function testExportObject(): void
    {
        $this->assertSame(new stdClass, new stdClass);
    }

    public function testExportResource(): void
    {
        $this->assertSame(fopen('php://memory', 'rw+'), fopen('php://memory', 'rw+'));
    }
}
