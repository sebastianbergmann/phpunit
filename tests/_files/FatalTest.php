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

use function extension_loaded;
use function phpversion;
use function version_compare;
use function xdebug_disable;
use PHPUnit\Framework\TestCase;

class FatalTest extends TestCase
{
    public function testFatalError(): void
    {
        if (extension_loaded('xdebug') && version_compare(phpversion('xdebug'), '3', '<')) {
            xdebug_disable();
        }

        eval('namespace PHPUnit\TestFixture { class FatalTest {} }');
    }
}
