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

use const E_USER_NOTICE;
use function error_get_last;
use function trigger_error;
use PHPUnit\Framework\TestCase;

final class SuppressedUserNoticeTest extends TestCase
{
    public function testSuppressedUserNotice(): void
    {
        $this->assertTrue(true);

        @trigger_error('message', E_USER_NOTICE);
    }

    public function testSuppressedUserNoticeErrorGetLast(): void
    {
        $this->assertNull(error_get_last());
        @trigger_error('message', E_USER_NOTICE);
        $this->assertIsArray(error_get_last());
    }
}
