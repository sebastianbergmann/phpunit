<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Issue6173;

use function error_log;
use PHPUnit\Framework\TestCase;

final class Issue6173Test extends TestCase
{
    public function test_log_success(): void
    {
        error_log('hello, success!');
        $this->assertTrue(true);
    }

    public function test_log_fail(): void
    {
        error_log('hello, fail!');
        $this->assertTrue(false);
    }
}
