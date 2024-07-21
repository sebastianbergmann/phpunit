<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Baseline;

use function trigger_error;
use PHPUnit\Framework\TestCase;

final class Test extends TestCase
{
    public function testUserErrors(): void
    {
        @trigger_error('deprecation', E_USER_DEPRECATED);
        trigger_error('warn', E_USER_WARNING);
        trigger_error('notice', E_USER_NOTICE);

        $this->assertTrue(true);
    }
}
