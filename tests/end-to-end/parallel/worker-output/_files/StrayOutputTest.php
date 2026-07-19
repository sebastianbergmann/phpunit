<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorkerOutput;

use const STDERR;
use function fwrite;
use function str_repeat;
use PHPUnit\Framework\TestCase;

final class StrayOutputTest extends TestCase
{
    public function testWritesMoreOutputToStandardErrorThanAPipeBuffers(): void
    {
        // Writing to STDERR bypasses PHPUnit's output buffering and reaches
        // the worker process' standard error directly. 256 KiB comfortably
        // exceeds the 64 KiB an operating system typically buffers for a
        // pipe, so this write would block the worker forever if its standard
        // error were a pipe that nobody reads.
        fwrite(STDERR, str_repeat('x', 256 * 1024));

        $this->assertTrue(true);
    }
}
