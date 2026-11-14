<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Code;

/**
 * A label with which TestCase::observe() observed numeric values of examples
 * of a property, with the number of values that were observed with it and
 * their statistics.
 *
 * The median and the 90th percentile are values that were observed: the
 * smallest ones that at least 50 and 90 percent of the values are not
 * greater than.
 *
 * @immutable
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final readonly class ObservedValues
{
    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @var positive-int
     */
    private int $numberOfValues;
    private float|int $minimum;
    private float|int $median;
    private float $mean;
    private float|int $percentile90;
    private float|int $maximum;

    /**
     * @param non-empty-string $label
     * @param positive-int     $numberOfValues
     *
     * @internal This method is not covered by the backward compatibility promise for PHPUnit
     */
    public function __construct(string $label, int $numberOfValues, float|int $minimum, float|int $median, float $mean, float|int $percentile90, float|int $maximum)
    {
        $this->label          = $label;
        $this->numberOfValues = $numberOfValues;
        $this->minimum        = $minimum;
        $this->median         = $median;
        $this->mean           = $mean;
        $this->percentile90   = $percentile90;
        $this->maximum        = $maximum;
    }

    /**
     * @return non-empty-string
     */
    public function label(): string
    {
        return $this->label;
    }

    /**
     * @return positive-int
     */
    public function numberOfValues(): int
    {
        return $this->numberOfValues;
    }

    public function minimum(): float|int
    {
        return $this->minimum;
    }

    public function median(): float|int
    {
        return $this->median;
    }

    public function mean(): float
    {
        return $this->mean;
    }

    public function percentile90(): float|int
    {
        return $this->percentile90;
    }

    public function maximum(): float|int
    {
        return $this->maximum;
    }
}
