<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\SelfTest\Basic;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Warning;

/**
 * @covers Foo
 *
 * @uses Bar
 *
 * @testdox Test result status with and without message
 */
class StatusTest extends TestCase
{
    public function testSuccess(): void
    {
        $this->createMock(\AnInterface::class);

        $this->assertTrue(true);
    }

    public function testFailure(): void
    {
        $this->assertTrue(false);
    }

    public function testError(): void
    {
        throw new \RuntimeException;
    }

    public function testIncomplete(): void
    {
        $this->markTestIncomplete();
    }

    public function testSkipped(): void
    {
        $this->markTestSkipped();
    }

    public function testRisky(): void
    {
    }

    public function testWarning(): void
    {
        throw new Warning;
    }

    public function testSuccessWithMessage(): void
    {
        $this->assertTrue(true, '"success with custom message"');
    }

    public function testFailureWithMessage(): void
    {
        $this->assertTrue(false, 'failure with custom message');
    }

    public function testErrorWithMessage(): void
    {
        throw new \RuntimeException('error with custom message');
    }

    public function testIncompleteWithMessage(): void
    {
        $this->markTestIncomplete('incomplete with custom message');
    }

    public function testSkippedWithMessage(): void
    {
        $this->markTestSkipped('skipped with custom message');
    }

    public function testRiskyWithMessage(): void
    {
        // Custom messages not implemented for risky status
    }

    public function testWarningWithMessage(): void
    {
        throw new Warning('warning with custom message');
    }
}
