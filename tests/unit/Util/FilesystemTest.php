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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Filesystem::class)]
#[Small]
final class FilesystemTest extends TestCase
{
    public function testCanResolvePathOrStream(): void
    {
        $this->assertSame('php://stdout', Filesystem::resolvePathOrStream('php://stdout'));
        $this->assertSame('socket://hostname:port', Filesystem::resolvePathOrStream('socket://hostname:port'));
        $this->assertSame(__FILE__, Filesystem::resolvePathOrStream(__FILE__));
        $this->assertFalse(Filesystem::resolvePathOrStream(__DIR__ . '/does-not-exist'));
    }
}
