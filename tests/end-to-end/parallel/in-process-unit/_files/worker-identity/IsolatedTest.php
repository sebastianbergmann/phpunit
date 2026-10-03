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
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
final class IsolatedTest extends TestCase
{
    public function testRunsInASeparateProcessOfTheMainProcessWithoutAWorkerIdentity(): void
    {
        $this->assertFalse(getenv('PHPUNIT_WORKER_ID'));
        $this->assertFalse(getenv('PHPUNIT_WORKER_TOKEN'));
    }
}
