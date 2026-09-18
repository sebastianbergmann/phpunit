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

        $this->assertSame(0, $queue->next([])->index());
    }

    public function testKeepsAsManyUnitsInSuiteOrderInFlightAsThereAreReservedSlots(): void
    {
        $queue = new DispatchQueue([$this->unit(9), $this->unit(8), $this->unit(1), $this->unit(0)]);

        $this->assertSame(0, $queue->next([])->index());

        // One reserved slot is held by the unit at index 0; the other one is
        // free, so the next unit in suite order goes out as well.
        $this->assertSame(1, $queue->next([0])->index());

        // Both reserved slots are occupied now, so the cost order has the
        // remaining dispatches.
        $this->assertSame(9, $queue->next([0, 1])->index());
    }

    public function testHandsOutTheLongestUnitWhileEveryReservedSlotIsOccupied(): void
    {
        // The units at index 0 and 1 have been dispatched already and are
        // executing; the queue holds the units that follow them.
        $queue = new DispatchQueue([$this->unit(9), $this->unit(5), $this->unit(2)]);

        // Both units that are executing precede everything still queued, so
        // both reserved slots are occupied from the first dispatch on.
        $this->assertSame(9, $queue->next([0, 1])->index());
        $this->assertSame(5, $queue->next([0, 1])->index());
    }

    public function testHandsOutTheUnitsTheOrderedOutputWaitsForOnceTheirPredecessorsHaveFinished(): void
    {
        $queue = new DispatchQueue([$this->unit(9), $this->unit(8), $this->unit(7), $this->unit(1), $this->unit(0)]);

        $this->assertSame(0, $queue->next([])->index());
        $this->assertSame(1, $queue->next([0])->index());
        $this->assertSame(9, $queue->next([0, 1])->index());

        // The units at index 0 and 1 have finished; of the units that are
        // executing, none precedes the unit at index 7, so both reserved slots
        // are free again.
        $this->assertSame(7, $queue->next([9])->index());
        $this->assertSame(8, $queue->next([9, 7])->index());
    }

    public function testHandsOutEveryUnitExactlyOnceAndIsEmptyAfterwards(): void
    {
        $queue = new DispatchQueue([$this->unit(3), $this->unit(0), $this->unit(2), $this->unit(1)]);

        $indexes = [];

        $indexes[] = $queue->next([])->index();
        $indexes[] = $queue->next([0])->index();
        $indexes[] = $queue->next([0, 1])->index();
        $indexes[] = $queue->next([])->index();

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
