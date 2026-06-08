<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ErrorHandler\DeprecationFilterOutsideTest;

use const E_USER_DEPRECATED;
use function trigger_error;
use PHPUnit\Framework\TestCase;

trigger_error('please ignore this deprecation', E_USER_DEPRECATED);
trigger_error('this deprecation must be reported', E_USER_DEPRECATED);

final class PassingTest extends TestCase
{
    public function testOne(): void
    {
        $this->assertTrue(true);
    }
}
