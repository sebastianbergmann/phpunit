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

/**
 * The number of units that may execute concurrently, shared by everything
 * that runs units side by side in a parallel run.
 *
 * The worker pool and the PHPT runner each start a unit only while a slot is
 * available and release the slot once the unit has finished. Sharing one
 * budget between them is what makes --parallel N mean "at most N concurrently
 * executing units" even when test classes and PHPT tests run side by side —
 * rather than N test classes plus another N PHPT tests.
 *
 * The budget counts executing units, not operating system processes: an idle
 * worker process does not hold a slot, because a parked worker costs nothing
 * while an executing unit occupies a CPU.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class ProcessBudget
{
    /**
     * @var positive-int
     */
    private readonly int $capacity;

    /**
     * @var non-negative-int
     */
    private int $inUse = 0;

    /**
     * @param positive-int $capacity
     */
    public function __construct(int $capacity)
    {
        $this->capacity = $capacity;
    }

    /**
     * Take one slot, when one is available. A caller that acquired a slot must
     * release() it once the unit it started has finished.
     */
    public function acquire(): bool
    {
        if ($this->inUse >= $this->capacity) {
            return false;
        }

        $this->inUse++;

        return true;
    }

    public function release(): void
    {
        assert($this->inUse > 0);

        $this->inUse--;
    }
}
