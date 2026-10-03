<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelCrashRestart;

use PHPUnit\Framework\TestCase;

final class BCrashingTest extends TestCase
{
    public function testThatPassesBeforeTheCrash(): void
    {
        $this->assertTrue(true);
    }

    public function testThatKillsTheWorkerProcess(): void
    {
        // Terminates the worker after the result of the first test was
        // streamed to the parent, so that the unit is not retried.
        exit(1);
    }
}
