<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\Logging;

use Exception;
use PHPUnit\Framework\TestCase;

final class TestWithMoreThanOneOutcomeTest extends TestCase
{
    protected function setUp(): void
    {
        if ($this->name() === 'testExceptionInSetUpAndAssertionFailureInTearDown') {
            throw new Exception('exception in setUp()');
        }

        if ($this->name() === 'testSkippedInSetUpAndExceptionInTearDown') {
            $this->markTestSkipped('skipped in setUp()');
        }
    }

    protected function tearDown(): void
    {
        if ($this->name() === 'testSuccessful') {
            return;
        }

        if ($this->name() === 'testSkippedAndExceptionInTearDown' ||
            $this->name() === 'testSkippedInSetUpAndExceptionInTearDown') {
            throw new Exception('exception in tearDown()');
        }

        $this->fail('assertion failure in tearDown()');
    }

    public function testExceptionAndAssertionFailureInTearDown(): void
    {
        throw new Exception('exception in test');
    }

    public function testSkippedAndExceptionInTearDown(): void
    {
        $this->markTestSkipped('skipped in test');
    }

    public function testAssertionFailureAndAssertionFailureInTearDown(): void
    {
        $this->fail('assertion failure in test');
    }

    public function testExceptionInSetUpAndAssertionFailureInTearDown(): void
    {
        $this->assertTrue(true);
    }

    public function testSkippedInSetUpAndExceptionInTearDown(): void
    {
        $this->assertTrue(true);
    }

    public function testSuccessful(): void
    {
        $this->assertTrue(true);
    }
}
