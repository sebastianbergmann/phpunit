<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace vendor\project;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Warning;

class StatusTest extends TestCase
{
    public function testSuccess(): void
    {
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
}
