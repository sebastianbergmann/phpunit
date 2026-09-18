<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Runner\Parallel;

use function sort;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(DispatchQueue::class)]
#[Small]
final class DispatchQueueTest extends TestCase
{
    public function testHandsOutTheUnitTheOrderedOutputWaitsForWhileNothingIsExecuting(): void
    {
        // The cost order puts the unit at index 0 last: it is the shortest.
        // Nothing is executing, so the results are released from index 0
        // onwards and that unit is the one the output waits for.
        $queue = new DispatchQueue([$this->unit(9), $this->unit(5), $this->unit(0)]);

        $this->assertSame(0, $queue->next(null)->index());
    }

    public function testHandsOutTheLongestUnitWhileTheUnitTheOutputWaitsForIsExecuting(): void
    {
        $queue = new DispatchQueue([$this->unit(9), $this->unit(5), $this->unit(0)]);

        $this->assertSame(0, $queue->next(null)->index());

        // The unit at index 0 is executing now, so it is what the output is
        // waiting for; the remaining dispatches are free to follow the cost
        // order.
        $this->assertSame(9, $queue->next(0)->index());
        $this->assertSame(5, $queue->next(0)->index());
    }

    public function testHandsOutTheUnitTheOrderedOutputWaitsForOnceItsPredecessorsHaveFinished(): void
    {
        $queue = new DispatchQueue([$this->unit(9), $this->unit(8), $this->unit(1), $this->unit(0)]);

        $this->assertSame(0, $queue->next(null)->index());
        $this->assertSame(9, $queue->next(0)->index());
        $this->assertSame(8, $queue->next(0)->index());

        // The unit at index 0 has finished; of the units that are executing,
        // the lowest index is 8, so the output now waits for the unit at
        // index 1 rather than for one of them.
        $this->assertSame(1, $queue->next(8)->index());
    }

    public function testHandsOutEveryUnitExactlyOnceAndIsEmptyAfterwards(): void
    {
        $queue = new DispatchQueue([$this->unit(3), $this->unit(0), $this->unit(2), $this->unit(1)]);

        $indexes = [];

        // Alternating between the two orders: every second dispatch is made
        // while the unit the output waits for is not executing.
        $indexes[] = $queue->next(null)->index();
        $indexes[] = $queue->next(0)->index();
        $indexes[] = $queue->next(null)->index();
        $indexes[] = $queue->next(0)->index();

        sort($indexes);

        $this->assertSame([0, 1, 2, 3], $indexes);
        $this->assertTrue($queue->isEmpty());
    }

    public function testIsEmptyWhenItHasNoUnits(): void
    {
        $this->assertTrue(new DispatchQueue([])->isEmpty());
    }

    public function testIsEmptyAfterItHasBeenCleared(): void
    {
        $queue = new DispatchQueue([$this->unit(0), $this->unit(1)]);

        $this->assertFalse($queue->isEmpty());

        $queue->clear();

        $this->assertTrue($queue->isEmpty());
    }

    /**
     * @param non-negative-int $index
     */
    private function unit(int $index): WorkUnit
    {
        $unit = $this->createMock(WorkUnit::class);

        $unit->method('index')->willReturn($index);

        return $unit;
    }
}
