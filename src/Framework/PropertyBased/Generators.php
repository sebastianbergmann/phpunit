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

use const PHP_FLOAT_MAX;
use const PHP_INT_MAX;
use const PHP_INT_MIN;
use function array_unique;
use function array_values;
use function enum_exists;
use function is_finite;
use function mb_check_encoding;
use function mb_str_split;
use function sprintf;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ArrayGenerator;
use PHPUnit\Framework\PropertyBased\Generator\BooleanGenerator;
use PHPUnit\Framework\PropertyBased\Generator\BytesGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ConstantGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ElementsGenerator;
use PHPUnit\Framework\PropertyBased\Generator\FloatGenerator;
use PHPUnit\Framework\PropertyBased\Generator\IntegerGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ListGenerator;
use PHPUnit\Framework\PropertyBased\Generator\NullableGenerator;
use PHPUnit\Framework\PropertyBased\Generator\OneOfGenerator;
use PHPUnit\Framework\PropertyBased\Generator\RecordGenerator;
use PHPUnit\Framework\PropertyBased\Generator\StringGenerator;
use PHPUnit\Framework\PropertyBased\Generator\TupleGenerator;
use UnitEnum;

/**
 * Factory for the generators that the parameters of properties are
 * generated with.
 */
final class Generators
{
    /**
     * Generates integers from $minimum to $maximum, biased towards the
     * boundaries of the range, towards the value closest to zero, and towards
     * small magnitudes.
     *
     * @throws InvalidGeneratorArgumentException
     *
     * @return ValueGenerator<int>
     */
    public static function integers(int $minimum = PHP_INT_MIN, int $maximum = PHP_INT_MAX): ValueGenerator
    {
        if ($minimum > $maximum) {
            throw new InvalidGeneratorArgumentException(
                sprintf(
                    'The minimum (%d) must not be greater than the maximum (%d)',
                    $minimum,
                    $maximum,
                ),
            );
        }

        return new IntegerGenerator($minimum, $maximum);
    }

    /**
     * Generates floating-point numbers from $minimum to $maximum, including
     * 0.0, -0.0, and very small and very large magnitudes.
     *
     * Positive infinity can only be generated when $maximum is PHP_FLOAT_MAX,
     * and negative infinity only when $minimum is -PHP_FLOAT_MAX.
     *
     * @throws InvalidGeneratorArgumentException
     *
     * @return ValueGenerator<float>
     */
    public static function floats(float $minimum = -PHP_FLOAT_MAX, float $maximum = PHP_FLOAT_MAX, bool $allowInfinity = false, bool $allowNan = false): ValueGenerator
    {
        if (!is_finite($minimum) || !is_finite($maximum)) {
            throw new InvalidGeneratorArgumentException(
                'The minimum and the maximum must be finite, use $allowInfinity to generate infinite values',
            );
        }

        if ($minimum > $maximum) {
            throw new InvalidGeneratorArgumentException(
                sprintf(
                    'The minimum (%F) must not be greater than the maximum (%F)',
                    $minimum,
                    $maximum,
                ),
            );
        }

        return new FloatGenerator($minimum, $maximum, $allowInfinity, $allowNan);
    }

    /**
     * @return ValueGenerator<bool>
     */
    public static function booleans(): ValueGenerator
    {
        return new BooleanGenerator;
    }

    /**
     * Generates valid UTF-8 strings of $minimumLength to $maximumLength
     * characters. $alphabet is a string of the characters to choose from.
     * Without it, the strings consist of any Unicode characters, including
     * ASCII, multibyte, and combining characters, and emoji.
     *
     * @throws InvalidGeneratorArgumentException
     *
     * @return ValueGenerator<string>
     */
    public static function strings(?string $alphabet = null, int $minimumLength = 0, ?int $maximumLength = null): ValueGenerator
    {
        self::ensureValidSize('length', $minimumLength, $maximumLength);

        if ($alphabet === null) {
            return new StringGenerator(null, $minimumLength, $maximumLength);
        }

        if ($alphabet === '' || !mb_check_encoding($alphabet, 'UTF-8')) {
            throw new InvalidGeneratorArgumentException(
                'The alphabet must be a non-empty, valid UTF-8 string',
            );
        }

        /** @var non-empty-list<non-empty-string> $characters */
        $characters = array_values(array_unique(mb_str_split($alphabet, 1, 'UTF-8')));

        return new StringGenerator($characters, $minimumLength, $maximumLength);
    }

    /**
     * Generates arbitrary binary strings of $minimumLength to $maximumLength
     * bytes.
     *
     * @throws InvalidGeneratorArgumentException
     *
     * @return ValueGenerator<string>
     */
    public static function bytes(int $minimumLength = 0, ?int $maximumLength = null): ValueGenerator
    {
        self::ensureValidSize('length', $minimumLength, $maximumLength);

        return new BytesGenerator($minimumLength, $maximumLength);
    }

    /**
     * Generates one of the given values. The first value is the simplest one.
     *
     * @template T
     *
     * @param T ...$values
     *
     * @throws InvalidGeneratorArgumentException
     *
     * @return ValueGenerator<T>
     */
    public static function elements(mixed ...$values): ValueGenerator
    {
        $values = array_values($values);

        if ($values === []) {
            throw new InvalidGeneratorArgumentException(
                'At least one value must be passed to elements()',
            );
        }

        return new ElementsGenerator($values);
    }

    /**
     * Generates the cases of the given enumeration. The first case is the
     * simplest one.
     *
     * @template T of UnitEnum
     *
     * @param class-string<T> $enumeration
     *
     * @throws InvalidGeneratorArgumentException
     *
     * @return ValueGenerator<T>
     */
    public static function cases(string $enumeration): ValueGenerator
    {
        if (!enum_exists($enumeration)) {
            throw new InvalidGeneratorArgumentException(
                sprintf(
                    '%s is not an enumeration',
                    $enumeration,
                ),
            );
        }

        $cases = $enumeration::cases();

        if ($cases === []) {
            throw new InvalidGeneratorArgumentException(
                sprintf(
                    'Enumeration %s has no cases',
                    $enumeration,
                ),
            );
        }

        return new ElementsGenerator($cases);
    }

    /**
     * @template T
     *
     * @param T $value
     *
     * @return ValueGenerator<T>
     */
    public static function constant(mixed $value): ValueGenerator
    {
        return new ConstantGenerator($value);
    }

    /**
     * Generates lists of $minimumSize to $maximumSize elements.
     *
     * @template T
     *
     * @param ValueGenerator<T> $elements
     *
     * @throws InvalidGeneratorArgumentException
     * @throws ValueGeneratorNotSupportedException
     *
     * @return ValueGenerator<list<T>>
     */
    public static function lists(ValueGenerator $elements, int $minimumSize = 0, ?int $maximumSize = null): ValueGenerator
    {
        self::ensureValidSize('size', $minimumSize, $maximumSize);

        return new ListGenerator(AbstractGenerator::from($elements), $minimumSize, $maximumSize);
    }

    /**
     * Generates arrays of $minimumSize to $maximumSize elements. Each key
     * occurs only once.
     *
     * @template K of array-key
     * @template V
     *
     * @param ValueGenerator<K> $keys
     * @param ValueGenerator<V> $values
     *
     * @throws InvalidGeneratorArgumentException
     * @throws ValueGeneratorNotSupportedException
     *
     * @return ValueGenerator<array<K, V>>
     */
    public static function arrays(ValueGenerator $keys, ValueGenerator $values, int $minimumSize = 0, ?int $maximumSize = null): ValueGenerator
    {
        self::ensureValidSize('size', $minimumSize, $maximumSize);

        return new ArrayGenerator(AbstractGenerator::from($keys), AbstractGenerator::from($values), $minimumSize, $maximumSize);
    }

    /**
     * Generates lists that consist of one value of each of the given
     * generators.
     *
     * @param ValueGenerator<mixed> ...$generators
     *
     * @throws ValueGeneratorNotSupportedException
     *
     * @return ValueGenerator<list<mixed>>
     */
    public static function tuples(ValueGenerator ...$generators): ValueGenerator
    {
        $tuple = [];

        foreach ($generators as $generator) {
            $tuple[] = AbstractGenerator::from($generator);
        }

        return new TupleGenerator($tuple);
    }

    /**
     * Generates arrays with the keys of $generators and a value of the
     * respective generator for each of them.
     *
     * @param array<array-key, ValueGenerator<mixed>> $generators
     *
     * @throws ValueGeneratorNotSupportedException
     *
     * @return ValueGenerator<array<array-key, mixed>>
     */
    public static function records(array $generators): ValueGenerator
    {
        $record = [];

        foreach ($generators as $key => $generator) {
            $record[$key] = AbstractGenerator::from($generator);
        }

        return new RecordGenerator($record);
    }

    /**
     * Generates null or a value of the given generator. null is the simplest
     * value.
     *
     * @template T
     *
     * @param ValueGenerator<T> $generator
     *
     * @throws ValueGeneratorNotSupportedException
     *
     * @return ValueGenerator<?T>
     */
    public static function nullable(ValueGenerator $generator): ValueGenerator
    {
        return new NullableGenerator(AbstractGenerator::from($generator));
    }

    /**
     * Generates a value of one of the given generators. The values of the
     * first generator are the simplest ones.
     *
     * @template T
     *
     * @param ValueGenerator<T> ...$generators
     *
     * @throws InvalidGeneratorArgumentException
     * @throws ValueGeneratorNotSupportedException
     *
     * @return ValueGenerator<T>
     */
    public static function oneOf(ValueGenerator ...$generators): ValueGenerator
    {
        $oneOf = [];

        foreach ($generators as $generator) {
            $oneOf[] = AbstractGenerator::from($generator);
        }

        if ($oneOf === []) {
            throw new InvalidGeneratorArgumentException(
                'At least one generator must be passed to oneOf()',
            );
        }

        return new OneOfGenerator($oneOf);
    }

    /**
     * @param 'length'|'size' $what
     *
     * @throws InvalidGeneratorArgumentException
     *
     * @phpstan-assert non-negative-int $minimum
     * @phpstan-assert ?non-negative-int $maximum
     */
    private static function ensureValidSize(string $what, int $minimum, ?int $maximum): void
    {
        if ($minimum < 0) {
            throw new InvalidGeneratorArgumentException(
                sprintf(
                    'The minimum %s (%d) must not be negative',
                    $what,
                    $minimum,
                ),
            );
        }

        if ($maximum !== null && $maximum < $minimum) {
            throw new InvalidGeneratorArgumentException(
                sprintf(
                    'The maximum %s (%d) must not be smaller than the minimum %s (%d)',
                    $what,
                    $maximum,
                    $what,
                    $minimum,
                ),
            );
        }
    }
}
