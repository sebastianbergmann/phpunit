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

use const PHP_FLOAT_MAX;
use function usort;
use PHPUnit\Runner\ResultCache\ResultCache;

/**
 * Orders the units of a chunk for dispatch: the longest first.
 *
 * The wall time of a chunk is bounded by the last unit to finish. Dispatching
 * the units with the longest recorded durations first makes the longest work
 * start as early as possible, so that it overlaps with the rest of the chunk
 * instead of becoming the straggler that the workers wait for at the end.
 *
 * The durations are those that the result cache recorded in a previous run. A
 * unit for which no duration is recorded — its tests have not run before — is
 * scheduled before every unit whose duration is known: its duration may be
 * arbitrarily large, and dispatching a small unit too early costs next to
 * nothing while dispatching a large unit too late costs its full duration.
 * Units whose estimates are equal keep their suite order; without a result
 * cache, every duration is unknown and the dispatch order is the suite order.
 *
 * Only the dispatch order is affected. Results are released in suite order
 * either way, so the output of the run does not change.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class Scheduler
{
    private ResultCache $resultCache;

    public function __construct(ResultCache $resultCache)
    {
        $this->resultCache = $resultCache;
    }

    /**
     * @template T of WorkUnit
     *
     * @param list<T> $units
     *
     * @return list<T>
     */
    public function schedule(array $units): array
    {
        $scheduled = [];

        foreach ($units as $unit) {
            $scheduled[] = [
                'duration' => $this->durationOf($unit),
                'unit'     => $unit,
            ];
        }

        usort(
            $scheduled,
            static function (array $a, array $b): int
            {
                return $b['duration'] <=> $a['duration'];
            },
        );

        $ordered = [];

        foreach ($scheduled as $entry) {
            $ordered[] = $entry['unit'];
        }

        return $ordered;
    }

    private function durationOf(WorkUnit $unit): float
    {
        $duration = $unit->duration($this->resultCache);

        // Nothing is recorded for this unit: its tests have not run before,
        // and their duration may be arbitrarily large.
        if ($duration === 0.0) {
            return PHP_FLOAT_MAX;
        }

        return $duration;
    }
}
