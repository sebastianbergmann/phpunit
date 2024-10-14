<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Event;

use function extension_loaded;
use function ini_get;
use PHPUnit\Framework\TestCase;

final class XdebugIsDisabled extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(extension_loaded('xdebug'));
        $this->assertSame('', (string) ini_get('xdebug.mode'));
    }
}
