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

use function assert;
use function count;
use function usort;

/**
 * The units of a chunk that have not been dispatched yet.
 *
 * Two orders are kept over the same units, and every dispatch chooses between
 * them.
 *
 * The first is the cost order the Scheduler established: the unit with the
 * longest recorded duration first, so that the longest work starts as early as
 * possible instead of becoming the straggler the workers wait for at the end
 * of the chunk.
 *
 * The second is the suite order, and it exists for the sake of the output. The
 * ResultAggregator releases results in suite order, and of the units that are
 * executing it reports only the one it is waiting for live; what every other
 * unit finishes is buffered until its own turn comes. Were the cost order the
 * only one, the unit the aggregator waits for — a short one, which the cost
 * order dispatches late — could be dispatched near the end of the chunk, and
 * the run would report nothing at all until then, only to report everything it
 * had buffered in one burst.
 *
 * The unit the aggregator waits for is therefore dispatched ahead of the cost
 * order whenever it is not in flight already: while no unit that precedes it in
 * suite order is executing, next() hands out the lowest-indexed unit that has
 * not been dispatched instead of the longest one. This costs exactly one
 * dispatch — with that unit in flight, the next call goes back to the cost
 * order — so that one slot walks the chunk in suite order and keeps the output
 * flowing while the others work the cost order for throughput.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class DispatchQueue
{
    /**
     * The units in cost order, longest first, as the scheduler ordered them.
     *
     * @var list<WorkUnit>
     */
    private array $byCost;

    /**
     * The same units in suite order.
     *
     * @var list<WorkUnit>
     */
    private array $bySuiteOrder;

    /**
     * The position of the next unit to consider in either order. Taking a unit
     * advances these cursors past the units that have been dispatched instead
     * of removing the unit from the lists, which would reindex all of the units
     * that remain on every dispatch.
     *
     * @var non-negative-int
     */
    private int $costPosition = 0;

    /**
     * @var non-negative-int
     */
    private int $suitePosition = 0;

    /**
     * The indexes of the units that have been handed out, so that a unit which
     * the one order has dispatched is skipped by the other.
     *
     * @var array<non-negative-int, true>
     */
    private array $dispatched = [];

    /**
     * @param list<WorkUnit> $units in cost order (see Scheduler)
     */
    public function __construct(array $units)
    {
        $bySuiteOrder = $units;

        usort(
            $bySuiteOrder,
            static function (WorkUnit $a, WorkUnit $b): int
            {
                return $a->index() <=> $b->index();
            },
        );

        $this->byCost       = $units;
        $this->bySuiteOrder = $bySuiteOrder;
    }

    /**
     * @phpstan-impure
     */
    public function isEmpty(): bool
    {
        return count($this->dispatched) === count($this->byCost);
    }

    /**
     * Take the next unit to dispatch.
     *
     * The lowest suite index among the units that are executing right now, or
     * null when none is, is what tells the queue whether the unit the
     * ResultAggregator waits for is in flight already: no unit executing with a
     * lower index means that the lowest-indexed unit which has not been
     * dispatched is the one the output is waiting for, and it is dispatched
     * ahead of the cost order.
     *
     * @param ?non-negative-int $lowestExecutingIndex
     */
    public function next(?int $lowestExecutingIndex): WorkUnit
    {
        assert(!$this->isEmpty());

        $nextInSuiteOrder = $this->nextInSuiteOrder();

        if ($lowestExecutingIndex === null || $nextInSuiteOrder->index() < $lowestExecutingIndex) {
            return $this->take($nextInSuiteOrder);
        }

        return $this->take($this->nextInCostOrder());
    }

    /**
     * Drop the units that have not been dispatched yet; the queue is empty
     * afterwards.
     */
    public function clear(): void
    {
        $this->byCost        = [];
        $this->bySuiteOrder  = [];
        $this->costPosition  = 0;
        $this->suitePosition = 0;
        $this->dispatched    = [];
    }

    private function nextInSuiteOrder(): WorkUnit
    {
        while ($this->wasDispatched($this->bySuiteOrder, $this->suitePosition)) {
            $this->suitePosition++;
        }

        assert(isset($this->bySuiteOrder[$this->suitePosition]));

        return $this->bySuiteOrder[$this->suitePosition];
    }

    private function nextInCostOrder(): WorkUnit
    {
        while ($this->wasDispatched($this->byCost, $this->costPosition)) {
            $this->costPosition++;
        }

        assert(isset($this->byCost[$this->costPosition]));

        return $this->byCost[$this->costPosition];
    }

    /**
     * Whether the unit at the given position of the given order has been
     * dispatched already — by the other order, in which case this order's
     * cursor has to advance past it.
     *
     * @param list<WorkUnit>   $units
     * @param non-negative-int $position
     */
    private function wasDispatched(array $units, int $position): bool
    {
        if (!isset($units[$position])) {
            return false;
        }

        return isset($this->dispatched[$units[$position]->index()]);
    }

    private function take(WorkUnit $unit): WorkUnit
    {
        $this->dispatched[$unit->index()] = true;

        return $unit;
    }
}
