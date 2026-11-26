<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ExpectErrorLog;

use function error_log;
use PHPUnit\Framework\TestCase;

class Foo
{
    public function doFoo()
    {
        error_log('logged a side effect');

        return '';
    }
}

final class ExpectErrorLogTest extends TestCase
{
    public function testOne(): void
    {
        $foo = new Foo;

        $this->assertSame('', $foo->doFoo());
        $this->expectErrorLog();
    }
}
