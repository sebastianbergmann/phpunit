<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Test;

use PHPUnit\Framework\RiskyTestError;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Warning;

final class HookTest extends TestCase
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
        throw new \Exception('message');
    }

    public function testIncomplete(): void
    {
        $this->markTestIncomplete('message');
    }

    public function testRisky(): void
    {
        throw new RiskyTestError('message');
    }

    public function testSkipped(): void
    {
        $this->markTestSkipped('message');
    }

    public function testWarning(): void
    {
        throw new Warning('message');
    }
}
