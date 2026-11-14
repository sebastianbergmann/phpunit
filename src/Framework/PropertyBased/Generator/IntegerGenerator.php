<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Generator;

use function assert;
use function log;
use function max;
use function min;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use SebastianBergmann\ChoiceSequence\BernoulliDistribution;
use SebastianBergmann\ChoiceSequence\BoundaryDistribution;

/**
 * Generates integers in the range [$minimum, $maximum]. The choice 0 stands
 * for the value closest to zero.
 *
 * A range that contains negative and non-negative values is drawn as two
 * choices: the first selects the non-negative (0) or the negative (1) half,
 * the second the value within it. The non-negative half is [0, $maximum]. In
 * the negative half, the choice c in [0, ~$minimum] stands for the value ~c.
 * This way, neither half can overflow.
 *
 * The more values a half has, the more often it is selected: the halves are
 * weighted by the binary logarithm of the number of their values, as the
 * values within a half are drawn so that each magnitude occurs. A half is
 * selected for at least a tenth of the values, however few values it has, as
 * these are close to a boundary of the range: for the range [-1, PHP_INT_MAX],
 * -1 is a tenth of the values rather than half of them.
 *
 * @template-extends AbstractGenerator<int>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class IntegerGenerator extends AbstractGenerator
{
    private const float MINIMUM_PROBABILITY_OF_A_HALF = 0.1;
    private int $minimum;
    private int $maximum;
    private BoundaryDistribution $magnitude;
    private BernoulliDistribution $half;

    /**
     * @var non-empty-string
     */
    private string $label;

    public function __construct(int $minimum, int $maximum)
    {
        $this->label = self::labelOf('integers', (string) $minimum, (string) $maximum);

        assert($minimum <= $maximum);

        $this->minimum   = $minimum;
        $this->maximum   = $maximum;
        $this->magnitude = new BoundaryDistribution;
        $this->half      = new BernoulliDistribution(self::probabilityOfNegativeHalf($minimum, $maximum));
    }

    public function label(): string
    {
        return $this->label;
    }

    protected function generateValue(ChoiceSource $source): int
    {
        if ($this->minimum >= 0) {
            $range = $this->maximum - $this->minimum;

            assert($range >= 0);

            return $this->minimum + $source->draw($range, $this->magnitude);
        }

        if ($this->maximum < 0) {
            $range = $this->maximum - $this->minimum;

            assert($range >= 0);

            return $this->maximum - $source->draw($range, $this->magnitude);
        }

        if ($source->draw(1, $this->half) === 0) {
            return $source->draw($this->maximum, $this->magnitude);
        }

        return ~$source->draw(~$this->minimum, $this->magnitude);
    }

    /**
     * The probability with which the negative half of a range that contains
     * negative and non-negative values is selected. The numbers of values are
     * calculated as floats, as a half can have more than PHP_INT_MAX values.
     */
    private static function probabilityOfNegativeHalf(int $minimum, int $maximum): float
    {
        if ($minimum >= 0 || $maximum < 0) {
            return 0.0;
        }

        $negative    = log((float) ~$minimum + 2.0, 2);
        $nonNegative = log((float) $maximum + 2.0, 2);

        return max(
            self::MINIMUM_PROBABILITY_OF_A_HALF,
            min(1.0 - self::MINIMUM_PROBABILITY_OF_A_HALF, $negative / ($negative + $nonNegative)),
        );
    }
}
