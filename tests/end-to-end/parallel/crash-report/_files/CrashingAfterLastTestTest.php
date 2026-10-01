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

use const STDERR;
use function fwrite;
use PHPUnit\Framework\TestCase;

final class CrashingAfterLastTestTest extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        // Terminates the worker after the results of both tests were
        // streamed to the parent, so that no test is left to report the
        // crash for.
        fwrite(STDERR, 'output of the worker process before it ended');

        exit(3);
    }

    public function testOne(): void
    {
        $this->assertTrue(true);
    }

    public function testTwo(): void
    {
        $this->assertTrue(true);
    }
}
