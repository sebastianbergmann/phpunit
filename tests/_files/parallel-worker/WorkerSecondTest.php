<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelWorker;

use function fopen;
use function fwrite;
use PHPUnit\Framework\TestCase;

final class WorkerSecondTest extends TestCase
{
    public function testThatWritesStrayOutputWithoutANewlineToTheControlChannel(): void
    {
        // Writes directly to the worker's control channel (file descriptor 1)
        // without a trailing newline, so that the stray output fuses with the
        // completion marker the worker appends on the same line. The parent
        // must still recognise the marker.
        $controlChannel = fopen('php://fd/1', 'wb');

        fwrite($controlChannel, 'stray output without a trailing newline');

        $this->assertTrue(true);
    }

    public function testSeesTheStateLeftBehindByTheFirstTest(): void
    {
        // Passes only when this test shares a process with WorkerFirstTest,
        // which is what running both tests in one persistent worker provides.
        $this->assertSame(1, WorkerProcessProbe::value());
        $this->assertSame(2, WorkerProcessProbe::increment());
    }

    public function testThatFails(): void
    {
        $this->assertTrue(false, 'intentional failure inside a persistent worker');
    }

    public function testThatKillsTheWorkerProcess(): void
    {
        // Terminates the worker before it can report a result, exercising the
        // parent's handling of a worker that dies in the middle of a test.
        exit(1);
    }
}
