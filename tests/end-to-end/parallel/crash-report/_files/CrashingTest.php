<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelCrashReport;

use PHPUnit\Framework\TestCase;

final class CrashingTest extends TestCase
{
    public function testThatPassesBeforeTheCrash(): void
    {
        $this->assertTrue(true);
    }

    public function testThatKillsTheWorkerProcess(): void
    {
        // Terminates the worker before it can report a result. Because the
        // result of the first test was already streamed to the parent, the
        // unit cannot be retried on a fresh worker; the crash is reported.
        exit(1);
    }

    public function testThatNeverRuns(): void
    {
        $this->assertTrue(true);
    }
}
