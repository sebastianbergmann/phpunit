<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Util;

use const DIRECTORY_SEPARATOR;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Filesystem::class)]
#[Small]
final class FilesystemTest extends TestCase
{
    public function testCanResolveStreamOrFile(): void
    {
        $this->assertSame('php://stdout', Filesystem::resolveStreamOrFile('php://stdout'));
        $this->assertSame('socket://hostname:port', Filesystem::resolveStreamOrFile('socket://hostname:port'));
        $this->assertSame(__FILE__, Filesystem::resolveStreamOrFile(__FILE__));
        $this->assertSame(__DIR__ . DIRECTORY_SEPARATOR . 'does-not-exist', Filesystem::resolveStreamOrFile(__DIR__ . '/does-not-exist'));
        $this->assertFalse(Filesystem::resolveStreamOrFile(__DIR__ . '/does-not-exist/does-not-exist'));
    }
}
