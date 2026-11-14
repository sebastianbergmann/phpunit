<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased;

use function array_key_exists;
use function array_sum;
use function assert;
use function ceil;
use function count;
use function end;
use function max;
use function sort;
use PHPUnit\Event\Code\ObservedValues;

/**
 * The numeric values that the examples of a property observed with
 * TestCase::observe(), by their label, and their statistics.
 *
 * The median and the 90th percentile are determined with the nearest-rank
 * method: the p-th percentile is the smallest of the values such that at
 * least p percent of the values are not greater than it. Like the minimum
 * and the maximum, they are among the values that were observed.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Observations
{
    /**
     * The values that were observed with each label, in the order in which
     * the labels were first used.
     *
     * @var array<non-empty-string, non-empty-list<float|int>>
     */
    private array $values = [];

    /**
     * @param list<array{label: non-empty-string, value: float|int}> $observations the values that an example observed
     */
    public function record(array $observations): void
    {
        foreach ($observations as $observation) {
            $this->values[$observation['label']][] = $observation['value'];
        }
    }

    /**
     * @return list<ObservedValues>
     */
    public function statistics(): array
    {
        $statistics = [];

        foreach ($this->values as $label => $values) {
            sort($values);

            $numberOfValues = count($values);
            $maximum        = end($values);

            $statistics[] = new ObservedValues(
                $label,
                $numberOfValues,
                $values[0],
                self::percentile($values, 50),
                (float) array_sum($values) / $numberOfValues,
                self::percentile($values, 90),
                $maximum,
            );
        }

        return $statistics;
    }

    /**
     * @param non-empty-list<float|int> $sorted
     * @param int<1, 100>               $percentile
     */
    private static function percentile(array $sorted, int $percentile): float|int
    {
        $index = max(1, (int) ceil($percentile * count($sorted) / 100)) - 1;

        assert(array_key_exists($index, $sorted));

        return $sorted[$index];
    }
}
