<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture\ParallelInProcessUnitWorkerIdentity;

use function getenv;
use PHPUnit\Framework\TestCase;

final class WorkerTest extends TestCase
{
    public function testRunsInAWorkerWithAWorkerIdentity(): void
    {
        $this->assertIsString(getenv('PHPUNIT_WORKER_ID'));
        $this->assertIsString(getenv('PHPUNIT_WORKER_TOKEN'));
    }
}
