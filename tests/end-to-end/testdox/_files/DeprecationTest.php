<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestDox;

use const E_USER_DEPRECATED;
use function trigger_error;
use PHPUnit\Framework\TestCase;

final class DeprecationTest extends TestCase
{
    public function testDeprecation(): void
    {
        trigger_error('deprecation', E_USER_DEPRECATED);

        $this->assertTrue(true);
    }
}
