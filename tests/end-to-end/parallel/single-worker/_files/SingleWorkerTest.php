<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelSingleWorker;

use function getenv;
use PHPUnit\Framework\TestCase;

final class SingleWorkerTest extends TestCase
{
    public function testRunsInTheMainProcess(): void
    {
        // A test executed by a parallel worker is told about that worker
        // through these environment variables; a test executed in the main
        // process, as it is without --parallel, sees neither of them.
        $this->assertFalse(getenv('PHPUNIT_WORKER_ID'));
        $this->assertFalse(getenv('PHPUNIT_WORKER_TOKEN'));
    }
}
