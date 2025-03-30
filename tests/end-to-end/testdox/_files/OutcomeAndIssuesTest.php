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
use const E_USER_NOTICE;
use const E_USER_WARNING;
use function trigger_error;
use Exception;
use PHPUnit\Framework\TestCase;

final class OutcomeAndIssuesTest extends TestCase
{
    public function testSuccess(): void
    {
        $this->assertTrue(true);
    }

    public function testSuccessButRisky(): void
    {
    }

    public function testSuccessButDeprecation(): void
    {
        $this->assertTrue(true);

        trigger_error('message', E_USER_DEPRECATED);
    }

    public function testSuccessButNotice(): void
    {
        $this->assertTrue(true);

        trigger_error('message', E_USER_NOTICE);
    }

    public function testSuccessButWarning(): void
    {
        $this->assertTrue(true);

        trigger_error('message', E_USER_WARNING);
    }

    public function testFailure(): void
    {
        $this->assertTrue(false);
    }

    public function testError(): void
    {
        throw new Exception('message');
    }

    public function testIncomplete(): void
    {
        $this->markTestIncomplete('message');
    }

    public function testSkipped(): void
    {
        $this->markTestSkipped('message');
    }
}
