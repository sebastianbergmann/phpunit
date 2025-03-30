<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestRunnerStopping;

use const E_USER_WARNING;
use function trigger_error;
use PHPUnit\Framework\TestCase;

final class WarningTest extends TestCase
{
    public function testOne(): void
    {
        trigger_error('message', E_USER_WARNING);

        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
