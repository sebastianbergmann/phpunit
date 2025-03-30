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

use const E_USER_ERROR;
use function trigger_error;
use PHPUnit\Framework\TestCase;

final class UserErrorTest extends TestCase
{
    public function testUserError(): void
    {
        $this->assertTrue(true);

        trigger_error('message', E_USER_ERROR);
    }

    public function testUserErrorMustAbortExecution(): void
    {
        trigger_error('message', E_USER_ERROR);
        $this->assertTrue(false);
    }
}
