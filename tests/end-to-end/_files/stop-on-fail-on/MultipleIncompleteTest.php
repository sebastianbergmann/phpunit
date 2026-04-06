<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\TestRunnerStopping;

use PHPUnit\Framework\TestCase;

final class MultipleIncompleteTest extends TestCase
{
    public function testOne(): void
    {
        $this->markTestIncomplete('message');
    }

    public function testTwo(): void
    {
        $this->markTestIncomplete('message');
    }

    public function testThree(): void
    {
        $this->markTestIncomplete('message');
    }

    public function testFour(): void
    {
        $this->assertTrue(true);
    }
}
