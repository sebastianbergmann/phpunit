<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

/**
 * @runTestsInSeparateProcesses
 */
final class TestSeperateProcesses extends TestCase
{
    protected function setUp(): void
    {
        print 'setUp output;';
    }

    protected function tearDown(): void
    {
        print 'tearDown output;';
    }

    public function testStdout(): void
    {
        print 'test output;';
        $this->assertTrue(false);
    }
}
