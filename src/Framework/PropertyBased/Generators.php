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

use const DEBUG_BACKTRACE_IGNORE_ARGS;
use const PHP_FLOAT_MAX;
use const PHP_INT_MAX;
use const PHP_INT_MIN;
use function array_is_list;
use function array_key_exists;
use function array_unique;
use function array_values;
use function class_exists;
use function count;
use function debug_backtrace;
use function enum_exists;
use function interface_exists;
use function is_finite;
use function is_int;
use function lcfirst;
use function mb_check_encoding;
use function mb_str_split;
use function sprintf;
use function var_export;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Distribution\WeightedRangesDistribution;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ArrayGenerator;
use PHPUnit\Framework\PropertyBased\Generator\BooleanGenerator;
use PHPUnit\Framework\PropertyBased\Generator\BytesGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ConstantGenerator;
use PHPUnit\Framework\PropertyBased\Generator\DeferredGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ElementsGenerator;
use PHPUnit\Framework\PropertyBased\Generator\FloatGenerator;
use PHPUnit\Framework\PropertyBased\Generator\IntegerGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ListGenerator;
use PHPUnit\Framework\PropertyBased\Generator\NullableGenerator;
use PHPUnit\Framework\PropertyBased\Generator\OneOfGenerator;
use PHPUnit\Framework\PropertyBased\Generator\RecordGenerator;
use PHPUnit\Framework\PropertyBased\Generator\StringGenerator;
use PHPUnit\Framework\PropertyBased\Generator\TupleGenerator;
use PHPUnit\Framework\PropertyBased\Type\NameScope;
use PHPUnit\Framework\PropertyBased\Type\TypeParser;
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
     * $recursive must be true when the elements can contain lists of this
     * generator, for instance in the generator that is registered with
     * #[GeneratorFor] for a class whose objects contain a list of objects of
     * the same class: another element is then added less often, so that the
     * generated values stay small. A generator that is derived from the type
     * of a parameter does this on its own.
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
    public static function lists(ValueGenerator $elements, int $minimumSize = 0, ?int $maximumSize = null, bool $recursive = false): ValueGenerator
    {
        self::ensureValidSize('size', $minimumSize, $maximumSize);

        return new ListGenerator(AbstractGenerator::from($elements), $minimumSize, $maximumSize, GeneratorDeriver::probabilityOfAnotherElement($recursive));
    }

    /**
     * Generates arrays of $minimumSize to $maximumSize elements. Each key
     * occurs only once. A string key that PHP stores as an integer, such as
     * '1', is not used, so that the keys are of the type that $keys
     * generates.
     *
     * $recursive must be true when the values can contain arrays of this
     * generator, as for lists().
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
    public static function arrays(ValueGenerator $keys, ValueGenerator $values, int $minimumSize = 0, ?int $maximumSize = null, bool $recursive = false): ValueGenerator
    {
        self::ensureValidSize('size', $minimumSize, $maximumSize);

        return new ArrayGenerator(AbstractGenerator::from($keys), AbstractGenerator::from($values), $minimumSize, $maximumSize, GeneratorDeriver::probabilityOfAnotherElement($recursive));
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
     * respective generator for each of them. A key in $optionalKeys is
     * sometimes omitted, which is simpler than adding it.
     *
     * @param array<array-key, ValueGenerator<mixed>> $generators
     * @param list<array-key>                         $optionalKeys
     *
     * @throws InvalidGeneratorArgumentException
     * @throws ValueGeneratorNotSupportedException
     *
     * @return ValueGenerator<array<array-key, mixed>>
     */
    public static function records(array $generators, array $optionalKeys = []): ValueGenerator
    {
        $record = [];

        foreach ($generators as $key => $generator) {
            $record[$key] = AbstractGenerator::from($generator);
        }

        foreach ($optionalKeys as $key) {
            if (!array_key_exists($key, $record)) {
                throw new InvalidGeneratorArgumentException(
                    sprintf(
                        'The optional key %s must be one of the keys of the record',
                        var_export($key, true),
                    ),
                );
            }
        }

        return new RecordGenerator($record, $optionalKeys);
    }

    /**
     * Generates null or a value of the given generator. null is the simplest
     * value.
     *
     * $recursive must be true when the values of $generator can contain values
     * of this generator, for instance in the generator that is registered with
     * #[GeneratorFor] for a class whose objects can refer to another object of
     * the same class: a value is then generated less often than null, so that
     * the generated values stay small. A generator that is derived from the
     * type of a parameter does this on its own.
     *
     * @template T
     *
     * @param ValueGenerator<T> $generator
     *
     * @throws ValueGeneratorNotSupportedException
     *
     * @return ValueGenerator<?T>
     */
    public static function nullable(ValueGenerator $generator, bool $recursive = false): ValueGenerator
    {
        if ($recursive) {
            return new NullableGenerator(AbstractGenerator::from($generator), GeneratorDeriver::PROBABILITY_OF_RECURSION);
        }

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
     * Generates a value of one of the given generators, each of which is
     * chosen with a probability that is proportional to its weight. Each
     * argument is a list of a weight and a generator:
     *
     *     Generators::frequency([9, Generators::integers()], [1, Generators::constant(null)])
     *
     * The values of the first generator are the simplest ones.
     *
     * @template T
     *
     * @param array{positive-int, ValueGenerator<T>} ...$weightedGenerators
     *
     * @throws InvalidGeneratorArgumentException
     * @throws ValueGeneratorNotSupportedException
     *
     * @return ValueGenerator<T>
     */
    public static function frequency(array ...$weightedGenerators): ValueGenerator
    {
        $generators = [];
        $ranges     = [];

        foreach (array_values($weightedGenerators) as $index => $weightedGenerator) {
            if (!array_is_list($weightedGenerator) ||
                count($weightedGenerator) !== 2 ||
                !is_int($weightedGenerator[0]) ||
                !$weightedGenerator[1] instanceof ValueGenerator) {
                throw new InvalidGeneratorArgumentException(
                    sprintf(
                        'Argument #%d of frequency() must be a list of a weight and a generator',
                        $index + 1,
                    ),
                );
            }

            if ($weightedGenerator[0] < 1) {
                throw new InvalidGeneratorArgumentException(
                    sprintf(
                        'The weight in argument #%d of frequency() (%d) must be positive',
                        $index + 1,
                        $weightedGenerator[0],
                    ),
                );
            }

            $generators[] = AbstractGenerator::from($weightedGenerator[1]);
            $ranges[]     = ['weight' => (float) $weightedGenerator[0], 'minimum' => $index, 'maximum' => $index];
        }

        if ($generators === []) {
            throw new InvalidGeneratorArgumentException(
                'At least one weighted generator must be passed to frequency()',
            );
        }

        return new OneOfGenerator($generators, new WeightedRangesDistribution($ranges));
    }

    /**
     * Generates objects of the given class the way they are generated for a
     * parameter of a property whose type is the class: with the generator
     * that the test case class registers for it with #[GeneratorFor], as
     * points in time or the cases of an enumeration, or with new, using
     * arguments that are generated for the parameters of the constructor.
     *
     * @template T of object
     *
     * @param class-string<T> $className
     *
     * @throws InvalidGeneratorArgumentException
     *
     * @return ValueGenerator<T>
     */
    public static function objects(string $className): ValueGenerator
    {
        if (!class_exists($className) && !interface_exists($className)) {
            throw new InvalidGeneratorArgumentException(
                sprintf(
                    '%s is neither a class nor an interface',
                    $className,
                ),
            );
        }

        /** @var DeferredGenerator<T> $generator */
        $generator = new DeferredGenerator(
            static fn (GeneratorDeriver $deriver): AbstractGenerator => $deriver->forClass($className),
            sprintf('objects of class %s', $className),
        );

        return $generator;
    }

    /**
     * Generates values the way they are generated for a parameter of a
     * property whose type, in its @param tag, is $type. Class names in the
     * type are resolved against the namespace and the imports that apply
     * where fromType() is called.
     *
     * @throws InvalidGeneratorArgumentException
     *
     * @return ValueGenerator<mixed>
     */
    public static function fromType(string $type): ValueGenerator
    {
        try {
            $parsed = (new TypeParser)->parse($type);
        } catch (InvalidTypeException $e) {
            throw new InvalidGeneratorArgumentException(
                sprintf(
                    'The type %s cannot be parsed: %s',
                    $type,
                    lcfirst($e->getMessage()),
                ),
                0,
                $e,
            );
        }

        $trace     = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $className = null;

        if (isset($trace[1]['class']) && class_exists($trace[1]['class'])) {
            $className = $trace[1]['class'];
        }

        $scope = NameScope::forCallSite($trace[0]['file'] ?? false, $trace[0]['line'] ?? false, $className);

        return new DeferredGenerator(
            static fn (GeneratorDeriver $deriver): AbstractGenerator => $deriver->forType($parsed, $scope),
            sprintf('values of type %s', $type),
        );
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
