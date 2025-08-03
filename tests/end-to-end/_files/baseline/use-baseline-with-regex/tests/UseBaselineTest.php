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
use function trigger_error;
use PHPUnit\Framework\TestCase;

final class UseBaselineTest extends TestCase
{
    public function testOne(): void
    {
        trigger_error('deprecation 123', E_USER_DEPRECATED);

        $this->assertTrue(true);
    }
}
