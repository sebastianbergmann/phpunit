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

/**
 * A test class whose first test finishes right away while its second one keeps
 * the unit running, so that the parent can poll a worker that has streamed the
 * events of a finished test but is not finished itself.
 */
final class WorkerStreamingTest extends TestCase
{
    public function testThatFinishesRightAway(): void
    {
        $this->assertTrue(true);
    }

    public function testThatSleeps(): void
    {
        usleep(5000000);

        $this->assertTrue(true);
    }
}
