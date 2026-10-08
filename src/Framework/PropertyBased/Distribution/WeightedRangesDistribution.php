<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Distribution;

use function count;
use function min;
use Random\Randomizer;

/**
 * Selects one of the given ranges of choices according to its weight and
 * draws a choice from it uniformly. Ranges that start above the maximum
 * are ignored, and ranges that extend beyond it are cut off.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class WeightedRangesDistribution implements Distribution
{
    /**
     * @var non-empty-list<array{weight: float, minimum: non-negative-int, maximum: non-negative-int}>
     */
    private array $ranges;

    /**
     * @param non-empty-list<array{weight: float, minimum: non-negative-int, maximum: non-negative-int}> $ranges
     */
    public function __construct(array $ranges)
    {
        $this->ranges = $ranges;
    }

    public function sample(Randomizer $randomizer, int $maximum): int
    {
        $ranges      = [];
        $totalWeight = 0.0;

        foreach ($this->ranges as $range) {
            if ($range['minimum'] > $maximum) {
                continue;
            }

            $ranges[] = $range;
            $totalWeight += $range['weight'];
        }

        if ($ranges === []) {
            return $randomizer->getInt(0, $maximum);
        }

        $selector = $randomizer->nextFloat() * $totalWeight;

        // rounding errors may leave the selector slightly above zero after
        // the weights of all ranges have been subtracted from it
        $selected = $ranges[count($ranges) - 1];

        foreach ($ranges as $range) {
            $selector -= $range['weight'];

            if ($selector < 0.0) {
                $selected = $range;

                break;
            }
        }

        return $randomizer->getInt($selected['minimum'], min($selected['maximum'], $maximum));
    }
}
