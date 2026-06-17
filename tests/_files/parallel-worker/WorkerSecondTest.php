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

use PHPUnit\Framework\TestCase;

final class WorkerSecondTest extends TestCase
{
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
