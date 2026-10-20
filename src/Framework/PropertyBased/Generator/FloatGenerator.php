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

use const INF;
use const NAN;
use const PHP_FLOAT_EPSILON;
use const PHP_FLOAT_MAX;
use const PHP_FLOAT_MIN;
use const PHP_INT_MAX;
use function abs;
use function assert;
use function floor;
use function is_finite;
use function is_float;
use function is_int;
use function is_nan;
use function max;
use function min;
use function pack;
use function unpack;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\Distribution\BernoulliDistribution;
use PHPUnit\Framework\PropertyBased\Distribution\BoundaryDistribution;
use PHPUnit\Framework\PropertyBased\Distribution\ExactDistribution;
use PHPUnit\Framework\PropertyBased\Distribution\UniformDistribution;

/**
 * Generates floating-point numbers from three choices: the sign (0 for
 * positive), whether the magnitude is an integer (0) or not (1), and the
 * magnitude.
 *
 * An integral magnitude is the choice itself, so that small integers are the
 * simplest values. Any other magnitude is encoded the way Hypothesis encodes
 * floating-point numbers: the exponents are ordered so that exponents closer
 * to zero come first, and the bits of the fractional part of the mantissa
 * are reversed, so that fractions with fewer bits are simpler.
 *
 * Generated values that are outside of the range are mapped into it, and
 * special values such as -0.0, the boundaries of the range, the smallest
 * subnormal number, and 2 ** 63 are generated with a significant probability.
 *
 * @template-extends AbstractGenerator<float>
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class FloatGenerator extends AbstractGenerator
{
    private const int LARGEST_INTEGRAL_MAGNITUDE     = 9007199254740992;
    private const int MANTISSA_BITS                  = 52;
    private const int MANTISSA_MASK                  = 4503599627370495;
    private const int EXPONENT_BIAS                  = 1023;
    private const int LARGEST_EXPONENT               = 2047;
    private const float PROBABILITY_OF_SPECIAL_VALUE = 0.2;
    private float $minimum;
    private float $maximum;
    private bool $allowPositiveInfinity;
    private bool $allowNegativeInfinity;
    private bool $allowNan;

    /**
     * @var non-empty-list<float>
     */
    private array $specialValues;
    private UniformDistribution $uniform;
    private BernoulliDistribution $kind;
    private BoundaryDistribution $integralMagnitude;

    /**
     * @var non-empty-string
     */
    private string $label;

    /**
     * @return array{0|1, 0|1, non-negative-int}
     */
    public static function encode(float $value): array
    {
        $sign = 0;

        if (self::bits($value) < 0) {
            $sign = 1;
        }

        $magnitude = abs($value);

        if (is_finite($magnitude) && $magnitude === floor($magnitude) && $magnitude <= self::LARGEST_INTEGRAL_MAGNITUDE) {
            $integralMagnitude = (int) $magnitude;

            assert($integralMagnitude >= 0);

            return [$sign, 0, $integralMagnitude];
        }

        $bits     = self::bits($magnitude);
        $exponent = $bits >> self::MANTISSA_BITS;
        $mantissa = self::updateMantissa($exponent - self::EXPONENT_BIAS, $bits & self::MANTISSA_MASK);
        $encoded  = (self::encodeExponent($exponent) << self::MANTISSA_BITS) | $mantissa;

        assert($encoded >= 0);

        return [$sign, 1, $encoded];
    }

    /**
     * @param non-negative-int $sign
     * @param non-negative-int $kind
     * @param non-negative-int $magnitude
     */
    public static function decode(int $sign, int $kind, int $magnitude): float
    {
        if ($kind === 0) {
            $value = (float) $magnitude;
        } else {
            $exponent = self::decodeExponent($magnitude >> self::MANTISSA_BITS);
            $mantissa = self::updateMantissa($exponent - self::EXPONENT_BIAS, $magnitude & self::MANTISSA_MASK);
            $value    = self::fromBits(($exponent << self::MANTISSA_BITS) | $mantissa);
        }

        if ($sign === 1) {
            return -$value;
        }

        return $value;
    }

    /**
     * Positive infinity can only be generated when $maximum is PHP_FLOAT_MAX,
     * and negative infinity only when $minimum is -PHP_FLOAT_MAX.
     */
    public function __construct(float $minimum, float $maximum, bool $allowInfinity, bool $allowNan)
    {
        assert(is_finite($minimum) && is_finite($maximum) && $minimum <= $maximum);

        $this->label = self::labelOf('floats', (string) $minimum, (string) $maximum, (string) (int) $allowInfinity, (string) (int) $allowNan);

        $this->minimum               = $minimum;
        $this->maximum               = $maximum;
        $this->allowPositiveInfinity = $allowInfinity && $maximum === PHP_FLOAT_MAX;
        $this->allowNegativeInfinity = $allowInfinity && $minimum === -PHP_FLOAT_MAX;
        $this->allowNan              = $allowNan;
        $this->specialValues         = $this->specialValues();
        $this->uniform               = new UniformDistribution;
        $this->kind                  = new BernoulliDistribution(0.5);
        $this->integralMagnitude     = new BoundaryDistribution;
    }

    public function label(): string
    {
        return $this->label;
    }

    protected function generateValue(ChoiceSource $source): float
    {
        $special = $source->suggest($this->specialValues, self::PROBABILITY_OF_SPECIAL_VALUE);

        if ($special !== null) {
            [$sign, $kind, $magnitude] = self::encode($special);

            $sign      = $source->draw(1, new ExactDistribution($sign));
            $kind      = $source->draw(1, new ExactDistribution($kind));
            $magnitude = $source->draw($this->largestMagnitude($kind), new ExactDistribution($magnitude));
        } else {
            $sign = $source->draw(1, $this->uniform);
            $kind = $source->draw(1, $this->kind);

            if ($kind === 0) {
                $magnitude = $source->draw(self::LARGEST_INTEGRAL_MAGNITUDE, $this->integralMagnitude);
            } else {
                $magnitude = $source->draw(PHP_INT_MAX, $this->uniform);
            }
        }

        return $this->intoRange(self::decode($sign, $kind, $magnitude));
    }

    /**
     * @return non-negative-int
     */
    private function largestMagnitude(int $kind): int
    {
        if ($kind === 0) {
            return self::LARGEST_INTEGRAL_MAGNITUDE;
        }

        return PHP_INT_MAX;
    }

    private function intoRange(float $value): float
    {
        if (is_nan($value)) {
            if ($this->allowNan) {
                return $value;
            }

            $value = 0.0;
        }

        if ($value === INF) {
            if ($this->allowPositiveInfinity) {
                return $value;
            }

            return $this->maximum;
        }

        if ($value === -INF) {
            if ($this->allowNegativeInfinity) {
                return $value;
            }

            return $this->minimum;
        }

        if ($this->isInRange($value)) {
            return $value;
        }

        // the fraction is 0 for 0.0, which is mapped to the boundary of the
        // range that is closest to zero, its simplest value
        $fraction = (self::bits(abs($value)) & self::MANTISSA_MASK) / self::MANTISSA_MASK;

        if ($this->maximum <= 0.0) {
            $value = $this->maximum * (1 - $fraction) + $this->minimum * $fraction;
        } else {
            $value = $this->minimum * (1 - $fraction) + $this->maximum * $fraction;
        }

        return max($this->minimum, min($this->maximum, $value));
    }

    /**
     * -0.0 is considered smaller than 0.0, so that it is not generated for a
     * range that starts at 0.0.
     */
    private function isInRange(float $value): bool
    {
        if ($value < $this->minimum || $value > $this->maximum) {
            return false;
        }

        if ($value !== 0.0) {
            return true;
        }

        if (self::isNegativeZero($value) && $this->minimum === 0.0 && !self::isNegativeZero($this->minimum)) {
            return false;
        }

        if (!self::isNegativeZero($value) && $this->maximum === 0.0 && self::isNegativeZero($this->maximum)) {
            return false;
        }

        return true;
    }

    /**
     * @return non-empty-list<float>
     */
    private function specialValues(): array
    {
        $candidates = [
            0.0,
            -0.0,
            1.0,
            -1.0,
            0.5,
            -0.5,
            1.5,
            0.1,
            -0.1,
            PHP_FLOAT_EPSILON,
            PHP_FLOAT_MIN,
            -PHP_FLOAT_MIN,
            4.9E-324,
            -4.9E-324,
            PHP_FLOAT_MAX,
            -PHP_FLOAT_MAX,
            9007199254740992.0,
            -9007199254740992.0,
            9.2233720368547758E+18,
            -9.2233720368547758E+18,
        ];

        $specialValues = [$this->minimum, $this->maximum];

        foreach ($candidates as $candidate) {
            if ($this->isInRange($candidate)) {
                $specialValues[] = $candidate;
            }
        }

        if ($this->allowPositiveInfinity) {
            $specialValues[] = INF;
        }

        if ($this->allowNegativeInfinity) {
            $specialValues[] = -INF;
        }

        if ($this->allowNan) {
            $specialValues[] = NAN;
        }

        return $specialValues;
    }

    private static function isNegativeZero(float $value): bool
    {
        return $value === 0.0 && self::bits($value) < 0;
    }

    private static function decodeExponent(int $encoded): int
    {
        if ($encoded <= 1023) {
            return self::EXPONENT_BIAS + $encoded;
        }

        if ($encoded < self::LARGEST_EXPONENT) {
            return self::EXPONENT_BIAS - 1 - ($encoded - 1024);
        }

        return self::LARGEST_EXPONENT;
    }

    private static function encodeExponent(int $exponent): int
    {
        if ($exponent === self::LARGEST_EXPONENT) {
            return self::LARGEST_EXPONENT;
        }

        if ($exponent >= self::EXPONENT_BIAS) {
            return $exponent - self::EXPONENT_BIAS;
        }

        return 1024 + (self::EXPONENT_BIAS - 1 - $exponent);
    }

    /**
     * Reverses the bits of the fractional part of the mantissa. Applying it
     * twice yields the original mantissa.
     */
    private static function updateMantissa(int $unbiasedExponent, int $mantissa): int
    {
        if ($unbiasedExponent <= 0) {
            return self::reverseBits($mantissa, self::MANTISSA_BITS);
        }

        if ($unbiasedExponent < self::MANTISSA_BITS) {
            $numberOfFractionalBits = self::MANTISSA_BITS - $unbiasedExponent;
            $fractionalPart         = $mantissa & ((1 << $numberOfFractionalBits) - 1);

            return ($mantissa ^ $fractionalPart) | self::reverseBits($fractionalPart, $numberOfFractionalBits);
        }

        return $mantissa;
    }

    private static function reverseBits(int $value, int $numberOfBits): int
    {
        $reversed = 0;

        for ($i = 0; $i < $numberOfBits; $i++) {
            $reversed = ($reversed << 1) | ($value & 1);
            $value >>= 1;
        }

        return $reversed;
    }

    private static function bits(float $value): int
    {
        $bits = unpack('J', pack('E', $value));

        assert($bits !== false && isset($bits[1]) && is_int($bits[1]));

        return $bits[1];
    }

    private static function fromBits(int $bits): float
    {
        $value = unpack('E', pack('J', $bits));

        assert($value !== false && isset($value[1]) && is_float($value[1]));

        return $value[1];
    }
}
