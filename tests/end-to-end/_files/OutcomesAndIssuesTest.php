<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use const E_USER_DEPRECATED;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use function trigger_error;
use Exception;
use PHPUnit\Framework\TestCase;

final class OutcomesAndIssuesTest extends TestCase
{
    public function testSuccessWithoutIssues(): void
    {
        $this->assertTrue(true);
    }

    public function testSuccessWithRisky(): void
    {
    }

    public function testSuccessWithDeprecation(): void
    {
        trigger_error('deprecation message', E_USER_DEPRECATED);

        $this->assertTrue(true);
    }

    public function testSuccessWithNotice(): void
    {
        trigger_error('notice message', E_USER_NOTICE);

        $this->assertTrue(true);
    }

    public function testSuccessWithWarning(): void
    {
        trigger_error('warning message', E_USER_WARNING);

        $this->assertTrue(true);
    }

    public function testFailWithDeprecation(): void
    {
        trigger_error('deprecation message', E_USER_DEPRECATED);

        $this->assertTrue(false);
    }

    public function testFailWithNotice(): void
    {
        trigger_error('notice message', E_USER_NOTICE);

        $this->assertTrue(false);
    }

    public function testFailWithWarning(): void
    {
        trigger_error('warning message', E_USER_WARNING);

        $this->assertTrue(false);
    }

    public function testErrorWithDeprecation(): void
    {
        trigger_error('deprecation message', E_USER_DEPRECATED);

        throw new Exception('exception message');
    }

    public function testErrorWithNotice(): void
    {
        trigger_error('notice message', E_USER_NOTICE);

        throw new Exception('exception message');
    }

    public function testErrorWithWarning(): void
    {
        trigger_error('warning message', E_USER_WARNING);

        throw new Exception('exception message');
    }

    public function testIncompleteWithDeprecation(): void
    {
        trigger_error('deprecation message', E_USER_DEPRECATED);

        $this->markTestIncomplete('incomplete message');
    }

    public function testIncompleteWithNotice(): void
    {
        trigger_error('notice message', E_USER_NOTICE);

        $this->markTestIncomplete('incomplete message');
    }

    public function testIncompleteWithWarning(): void
    {
        trigger_error('warning message', E_USER_WARNING);

        $this->markTestIncomplete('incomplete message');
    }

    public function testSkippedWithDeprecation(): void
    {
        trigger_error('deprecation message', E_USER_DEPRECATED);

        $this->markTestSkipped('skipped message');
    }

    public function testSkippedWithNotice(): void
    {
        trigger_error('notice message', E_USER_NOTICE);

        $this->markTestSkipped('skipped message');
    }

    public function testSkippedWithWarning(): void
    {
        trigger_error('warning message', E_USER_WARNING);

        $this->markTestSkipped('skipped message');
    }
}
