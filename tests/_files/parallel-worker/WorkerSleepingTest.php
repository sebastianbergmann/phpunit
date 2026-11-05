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

use function usleep;
use PHPUnit\Framework\TestCase;

final class WorkerSleepingTest extends TestCase
{
    public function testThatSleeps(): void
    {
        usleep(5000000);

        $this->assertTrue(true);
    }
}
