<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Baseline;

use const E_USER_DEPRECATED;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use function strlen;
use function trigger_error;
use PHPUnit\Framework\TestCase;

final class UseBaselineTest extends TestCase
{
    public function testOne(): void
    {
        strlen(null);

        $f = static function (): void
        {
        };

        $a  = &$f();

        $a = $b;

        trigger_error('deprecation', E_USER_DEPRECATED);
        trigger_error('notice', E_USER_NOTICE);
        trigger_error('warning', E_USER_WARNING);

        $this->assertTrue(true);
    }
}
