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

/**
 * A test class whose first test passes, and whose events are therefore
 * streamed to the parent, while its second test kills the worker process.
 */
final class WorkerPassesAndThenDiesTest extends TestCase
{
    public function testThatPasses(): void
    {
        $this->assertTrue(true);
    }

    public function testThatKillsTheWorkerProcess(): void
    {
        exit(1);
    }
}
