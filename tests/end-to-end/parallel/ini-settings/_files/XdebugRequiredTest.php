<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelIniSettings;

use function header;
use function ob_get_clean;
use function ob_start;
use function xdebug_get_headers;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

final class XdebugRequiredTest extends TestCase
{
    #[RequiresPhpExtension('xdebug')]
    public function testUsesXdebug(): void
    {
        ob_start();
        header('X-Test: Testing');
        print 'output';
        $content = ob_get_clean();

        $this->assertSame('output', $content);
        $this->assertSame(['X-Test: Testing'], xdebug_get_headers());
    }
}
