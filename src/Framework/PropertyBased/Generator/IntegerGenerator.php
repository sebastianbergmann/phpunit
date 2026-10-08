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
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\Distribution\BoundaryDistribution;
use PHPUnit\Framework\PropertyBased\Distribution\UniformDistribution;

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
 * @template-extends AbstractGenerator<int>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class IntegerGenerator extends AbstractGenerator
{
    private int $minimum;
    private int $maximum;
    private BoundaryDistribution $magnitude;
    private UniformDistribution $half;

    public function __construct(int $minimum, int $maximum)
    {
        assert($minimum <= $maximum);

        $this->minimum   = $minimum;
        $this->maximum   = $maximum;
        $this->magnitude = new BoundaryDistribution;
        $this->half      = new UniformDistribution;
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
}
