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
use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_VALIDATE_IP;
use const PHP_FLOAT_MAX;
use const PHP_FLOAT_MIN;
use const PHP_INT_MAX;
use const PHP_INT_MIN;
use function array_is_list;
use function array_key_exists;
use function array_unique;
use function array_values;
use function assert;
use function class_exists;
use function count;
use function debug_backtrace;
use function enum_exists;
use function filter_var;
use function get_debug_type;
use function inet_pton;
use function interface_exists;
use function ip2long;
use function is_finite;
use function is_int;
use function lcfirst;
use function long2ip;
use function mb_check_encoding;
use function mb_str_split;
use function preg_match;
use function sprintf;
use function var_export;
use Closure;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ArrayGenerator;
use PHPUnit\Framework\PropertyBased\Generator\BooleanGenerator;
use PHPUnit\Framework\PropertyBased\Generator\BytesGenerator;
use PHPUnit\Framework\PropertyBased\Generator\CodePointRanges;
use PHPUnit\Framework\PropertyBased\Generator\ConstantGenerator;
use PHPUnit\Framework\PropertyBased\Generator\DateTimeGenerator;
use PHPUnit\Framework\PropertyBased\Generator\DeferredGenerator;
use PHPUnit\Framework\PropertyBased\Generator\DomainNameGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ElementsGenerator;
use PHPUnit\Framework\PropertyBased\Generator\EmailAddressGenerator;
use PHPUnit\Framework\PropertyBased\Generator\FloatGenerator;
use PHPUnit\Framework\PropertyBased\Generator\IntegerGenerator;
use PHPUnit\Framework\PropertyBased\Generator\Ipv4AddressGenerator;
use PHPUnit\Framework\PropertyBased\Generator\Ipv6AddressGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ListGenerator;
use PHPUnit\Framework\PropertyBased\Generator\NullableGenerator;
use PHPUnit\Framework\PropertyBased\Generator\OneOfGenerator;
use PHPUnit\Framework\PropertyBased\Generator\RecordGenerator;
use PHPUnit\Framework\PropertyBased\Generator\RegularExpressionGenerator;
use PHPUnit\Framework\PropertyBased\Generator\StringGenerator;
use PHPUnit\Framework\PropertyBased\Generator\TupleGenerator;
use PHPUnit\Framework\PropertyBased\Generator\UrlGenerator;
use PHPUnit\Framework\PropertyBased\Generator\UuidGenerator;
use PHPUnit\Framework\PropertyBased\RegularExpression\Parser;
use PHPUnit\Framework\PropertyBased\Type\NameScope;
use PHPUnit\Framework\PropertyBased\Type\TypeParser;
use SebastianBergmann\ChoiceSequence\WeightedRangesDistribution;
use UnitEnum;

/**
 * Factory for the generators that the parameters of properties are
 * generated with.
 */
final class Generators
{
    /**
     * Time zones whose offset from UTC or whose transitions between standard
     * time and daylight saving time are notable.
     *
     * @var non-empty-list<non-empty-string>
     */
    private const array NOTABLE_TIMEZONES = [
        'UTC',
        // the largest and the smallest offset from UTC
        'Pacific/Kiritimati',
        'Pacific/Pago_Pago',
        // offsets that are not whole hours
        'Asia/Kathmandu',
        'Asia/Tehran',
        'America/St_Johns',
        'Pacific/Chatham',
        // daylight saving time of 30 minutes, of two hours, and negative
        // daylight saving time
        'Australia/Lord_Howe',
        'Antarctica/Troll',
        'Europe/Dublin',
        // daylight saving time in the southern hemisphere, and daylight
        // saving time that is suspended during Ramadan
        'America/Santiago',
        'Africa/Casablanca',
        // the day that was skipped when the time zone crossed the
        // international date line
        'Pacific/Apia',
        // time zones that are used often
        'America/New_York',
        'Europe/London',
        'Asia/Tokyo',
    ];

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
     * Positive infinity can only be generated when $maximum is PHP_FLOAT_MAX
     * and not excluded, and negative infinity only when $minimum is
     * -PHP_FLOAT_MAX and not excluded.
     *
     * With $excludeMinimum or $excludeMaximum, the range does not include the
     * respective bound, but the next float towards the other bound. Without
     * $allowSubnormals, no subnormal float, whose magnitude is smaller than
     * PHP_FLOAT_MIN, is generated: it is replaced with 0.0 or -0.0.
     *
     * @throws InvalidGeneratorArgumentException
     *
     * @return ValueGenerator<float>
     */
    public static function floats(float $minimum = -PHP_FLOAT_MAX, float $maximum = PHP_FLOAT_MAX, bool $allowInfinity = false, bool $allowNan = false, bool $excludeMinimum = false, bool $excludeMaximum = false, bool $allowSubnormals = true): ValueGenerator
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

        // without subnormal floats, the next float after an excluded bound is
        // the next one that is not subnormal
        if ($excludeMinimum) {
            $minimum = FloatGenerator::nextUp($minimum);

            if (!$allowSubnormals && FloatGenerator::isSubnormal($minimum)) {
                $minimum = self::nextFloatThatIsNotSubnormal($minimum, PHP_FLOAT_MIN, -0.0);
            }
        }

        if ($excludeMaximum) {
            $maximum = FloatGenerator::nextDown($maximum);

            if (!$allowSubnormals && FloatGenerator::isSubnormal($maximum)) {
                $maximum = self::nextFloatThatIsNotSubnormal($maximum, 0.0, -PHP_FLOAT_MIN);
            }
        }

        if ($minimum > $maximum) {
            throw new InvalidGeneratorArgumentException(
                'The range contains no float when its excluded bounds are left out',
            );
        }

        if (!$allowSubnormals && (FloatGenerator::isSubnormal($minimum) || FloatGenerator::isSubnormal($maximum))) {
            throw new InvalidGeneratorArgumentException(
                'The minimum and the maximum must not be subnormal when subnormal floats are not allowed',
            );
        }

        return new FloatGenerator($minimum, $maximum, $allowInfinity, $allowNan, $allowSubnormals);
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
     * Alternatively, $codePointRanges are the ranges of the code points of
     * the characters to choose from, as lists of their first and their last
     * code point, for instance [[0x41, 0x5A], [0x30, 0x39]]; the characters of
     * each range are chosen about equally often, and surrogates are left out.
     * Without either, the strings consist of any Unicode characters, including
     * ASCII, multibyte, and combining characters, and emoji.
     *
     * @param ?list<array{0: int, 1: int}> $codePointRanges
     *
     * @throws InvalidGeneratorArgumentException
     *
     * @return ValueGenerator<string>
     */
    public static function strings(?string $alphabet = null, int $minimumLength = 0, ?int $maximumLength = null, ?array $codePointRanges = null): ValueGenerator
    {
        self::ensureValidSize('length', $minimumLength, $maximumLength);

        if ($codePointRanges !== null) {
            if ($alphabet !== null) {
                throw new InvalidGeneratorArgumentException(
                    'Either an alphabet or code point ranges can be specified, not both',
                );
            }

            return new StringGenerator(null, $minimumLength, $maximumLength, CodePointRanges::from($codePointRanges));
        }

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
     * Generates strings that the given regular expression, with its
     * delimiters and modifiers, matches as a whole, for instance
     * '/\A[a-z]+-\d{2,4}\z/'.
     *
     * Literal and escaped characters, the dot, character classes, the escape
     * sequences \d, \D, \w, \W, \s, \S, \h, \H, \v, and \V, quantifiers,
     * groups, alternation, \Q...\E, comments, and all modifiers are
     * supported. Anchors and the other assertions that consume no characters,
     * such as ^, $, and \b, are supported where they hold for the strings that
     * are generated, at their start or end, for instance; an example is
     * rejected when the regular expression does not match the generated
     * string. Lookahead and lookbehind assertions, backreferences, recursion,
     * conditional subpatterns, inline options, and Unicode properties are not
     * supported.
     *
     * With the u modifier, the strings are valid UTF-8, and \d, \w, and \s, as
     * well as \D, \W, and \S, only generate ASCII characters. Without it, the
     * strings are binary strings: the dot and negated character classes can
     * generate any byte. The simplest string consists of the simplest
     * characters, the minimum number of repetitions, and the first
     * alternatives.
     *
     * @throws InvalidGeneratorArgumentException
     *
     * @return ValueGenerator<string>
     */
    public static function fromRegularExpression(string $regularExpression): ValueGenerator
    {
        if ($regularExpression === '' || @preg_match($regularExpression, '') === false) {
            throw new InvalidGeneratorArgumentException(
                sprintf(
                    '%s is not a valid regular expression',
                    $regularExpression,
                ),
            );
        }

        return new RegularExpressionGenerator($regularExpression, new Parser($regularExpression)->parse());
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
     * When $unique is true, no element of a list equals another one. Objects
     * are equal when they are of the same class and assertEquals() considers
     * them equal, other values when they are identical. The example is rejected when
     * $elements does not generate enough different values for $minimumSize.
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
    public static function lists(ValueGenerator $elements, int $minimumSize = 0, ?int $maximumSize = null, bool $recursive = false, bool $unique = false): ValueGenerator
    {
        self::ensureValidSize('size', $minimumSize, $maximumSize);

        return new ListGenerator(AbstractGenerator::from($elements), $minimumSize, $maximumSize, GeneratorDeriver::probabilityOfAnotherElement($recursive), $unique);
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
     * Generates the values of the generator that $generator returns, which is
     * called when the first value is generated rather than now. This way, a
     * generator can refer to itself, for values that contain values of the
     * same kind:
     *
     *     $values = Generators::deferred(
     *         static function () use (&$values): ValueGenerator
     *         {
     *             return Generators::oneOf(
     *                 Generators::integers(),
     *                 Generators::lists($values, recursive: true),
     *             );
     *         },
     *     );
     *
     * A generator that refers to itself has to make a choice before it does,
     * as oneOf() does here, and it has to pass true for $recursive to lists(),
     * arrays(), and nullable(), so that the generated values stay small.
     *
     * @template T
     *
     * @param Closure(): ValueGenerator<T> $generator
     *
     * @return ValueGenerator<T>
     */
    public static function deferred(Closure $generator): ValueGenerator
    {
        /** @var DeferredGenerator<T> $deferred */
        $deferred = new DeferredGenerator(
            static fn (GeneratorDeriver $deriver): AbstractGenerator => self::generatorReturnedBy($generator(), 'deferred'),
            'the values of a generator that was created with Generators::deferred()',
        );

        return $deferred;
    }

    /**
     * Generates the values of $base and values that $extend builds from them,
     * nested up to $maximumDepth levels. $extend receives a generator for the
     * values of one level and returns a generator for the values of the next
     * level, which contain them:
     *
     *     Generators::recursive(
     *         Generators::integers(),
     *         static fn (ValueGenerator $values): ValueGenerator => Generators::lists($values),
     *     );
     *
     * generates integers, lists of integers, lists of lists of integers, and
     * so on. The values of $base are generated as often as the values of all
     * other levels together, and they are the simplest ones.
     *
     * @param ValueGenerator<mixed>                                 $base
     * @param Closure(ValueGenerator<mixed>): ValueGenerator<mixed> $extend
     *
     * @throws InvalidGeneratorArgumentException
     * @throws ValueGeneratorNotSupportedException
     *
     * @return ValueGenerator<mixed>
     */
    public static function recursive(ValueGenerator $base, Closure $extend, int $maximumDepth = 5): ValueGenerator
    {
        if ($maximumDepth < 0) {
            throw new InvalidGeneratorArgumentException(
                sprintf(
                    'The maximum depth (%d) must not be negative',
                    $maximumDepth,
                ),
            );
        }

        $baseGenerator = AbstractGenerator::from($base);

        if ($maximumDepth === 0) {
            return $baseGenerator;
        }

        // every level, the deepest one included, chooses between the base
        // generator and the next level, so that its values are drawn the same
        // way, and the shrinker can replace the span of a value with the span
        // of a value it contains, which has the same label
        $label     = AbstractGenerator::labelOf('recursive', $baseGenerator->label());
        $generator = new OneOfGenerator([$baseGenerator], null, $label);

        for ($depth = 1; $depth <= $maximumDepth; $depth++) {
            $generator = new OneOfGenerator([$baseGenerator, self::generatorReturnedBy($extend($generator), 'recursive')], null, $label);
        }

        return $generator;
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
     * Generates UUIDs (RFC 9562) of the versions in $versions, in their
     * canonical form: lowercase hexadecimal digits in groups of 8, 4, 4, 4,
     * and 12. The version and the variant (RFC 9562) are set, the other bits
     * are biased towards all zeros and all ones in each field. The timestamp
     * of versions 1, 6, and 7 is one field, which is laid out as the version
     * requires, so that it shrinks towards the epoch of the version. The bits
     * of the other versions are not derived from anything: a UUID of version
     * 3 or 5 is not the hash of a name, for instance.
     *
     * With $nilAndMax, the nil UUID and the max UUID are generated as well,
     * which do not have a version.
     *
     * The first version is the simplest one, and the simplest UUID of a
     * version has only zeros in its other bits, for instance
     * 00000000-0000-4000-8000-000000000000 for version 4. With $nilAndMax,
     * the nil UUID is the simplest one.
     *
     * @param list<int> $versions
     *
     * @throws InvalidGeneratorArgumentException
     *
     * @return ValueGenerator<non-empty-string>
     */
    public static function uuids(array $versions = [1, 2, 3, 4, 5, 6, 7, 8], bool $nilAndMax = false): ValueGenerator
    {
        if ($versions === []) {
            throw new InvalidGeneratorArgumentException('At least one version of UUIDs must be specified');
        }

        $valid = [];

        foreach ($versions as $version) {
            if ($version < 1 || $version > 8) {
                throw new InvalidGeneratorArgumentException(
                    sprintf(
                        'The version of UUIDs (%d) must be from 1 to 8',
                        $version,
                    ),
                );
            }

            $valid[] = $version;
        }

        return new UuidGenerator(array_values(array_unique($valid)), $nilAndMax);
    }

    /**
     * Generates IPv4 addresses of $network, given in CIDR notation, in dotted
     * decimal notation without leading zeros.
     *
     * Half of the addresses are from the special-purpose ranges (RFC 6890)
     * that $network overlaps, such as the private networks, loopback,
     * link-local, multicast, and the networks for documentation, or are right
     * next to one of them. The addresses are biased towards the first and the
     * last address of $network and of each range.
     *
     * The simplest address is the network address of $network, 0.0.0.0 by
     * default.
     *
     * @throws InvalidGeneratorArgumentException
     *
     * @return ValueGenerator<non-empty-string>
     */
    public static function ipv4Addresses(string $network = '0.0.0.0/0'): ValueGenerator
    {
        if (preg_match('/^([0-9.]+)\/(0|[1-9][0-9]?)$/', $network, $matches) !== 1 ||
            filter_var($matches[1], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false ||
            (int) $matches[2] > 32) {
            throw new InvalidGeneratorArgumentException(
                sprintf(
                    'The network (%s) must be an IPv4 address and the length of its prefix, from 0 to 32, in CIDR notation, for instance 192.168.0.0/16',
                    $network,
                ),
            );
        }

        $address      = ip2long($matches[1]);
        $prefixLength = (int) $matches[2];

        assert($address !== false && $prefixLength >= 0);

        $hostBits = (1 << (32 - $prefixLength)) - 1;

        if (($address & $hostBits) !== 0) {
            throw new InvalidGeneratorArgumentException(
                sprintf(
                    'The address of the network (%s) must be its network address, whose host bits are zero: %s/%d',
                    $network,
                    long2ip($address & ~$hostBits),
                    $prefixLength,
                ),
            );
        }

        return new Ipv4AddressGenerator($address, $prefixLength);
    }

    /**
     * Generates IPv6 addresses of $network, given in CIDR notation, in their
     * canonical text representation (RFC 5952): lowercase hexadecimal digits
     * without leading zeros, the first longest run of at least two groups of
     * zeros compressed to "::", and the last 32 bits of an IPv4-mapped
     * address in dotted decimal notation. No zone identifier is generated.
     *
     * Half of the addresses are from the special-purpose ranges (RFC 6890)
     * in $network, such as the unspecified address, loopback, IPv4-mapped
     * addresses, the network for documentation, unique local addresses,
     * link-local addresses, and multicast. Half of the addresses have a run
     * of zeros of any length and at any position. The groups of 16 bits are
     * biased towards all zeros and all ones.
     *
     * The simplest address is the network address of $network, :: by default.
     *
     * @throws InvalidGeneratorArgumentException
     *
     * @return ValueGenerator<non-empty-string>
     */
    public static function ipv6Addresses(string $network = '::/0'): ValueGenerator
    {
        if (preg_match('/^([0-9A-Fa-f:.]+)\/(0|[1-9][0-9]{0,2})$/', $network, $matches) !== 1 ||
            filter_var($matches[1], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false ||
            (int) $matches[2] > 128) {
            throw new InvalidGeneratorArgumentException(
                sprintf(
                    'The network (%s) must be an IPv6 address and the length of its prefix, from 0 to 128, in CIDR notation, for instance 2001:db8::/32',
                    $network,
                ),
            );
        }

        $bytes        = inet_pton($matches[1]);
        $prefixLength = (int) $matches[2];

        assert($bytes !== false && $prefixLength >= 0);

        $groups        = Ipv6AddressGenerator::groupsOf($bytes);
        $networkGroups = Ipv6AddressGenerator::withoutHostBits($groups, $prefixLength);

        if ($networkGroups !== $groups) {
            throw new InvalidGeneratorArgumentException(
                sprintf(
                    'The address of the network (%s) must be its network address, whose host bits are zero: %s/%d',
                    $network,
                    Ipv6AddressGenerator::format($networkGroups),
                    $prefixLength,
                ),
            );
        }

        return new Ipv6AddressGenerator($groups, $prefixLength);
    }

    /**
     * Generates domain names of at most $maximumLength characters, which
     * consist of letters, digits, and hyphens (RFC 1123), in lowercase and
     * without a trailing dot.
     *
     * A domain name has at least two labels, each with 1 to 63 characters,
     * neither starting nor ending with a hyphen, and without hyphens as their
     * third and fourth character, which are reserved for internationalized
     * domain names. The top-level domain has at least two letters and no
     * other characters. Labels of 63 characters, labels of digits only,
     * domain names of many labels, and domain names of $maximumLength
     * characters are generated often.
     *
     * The simplest domain name is a.aa.
     *
     * @throws InvalidGeneratorArgumentException
     *
     * @return ValueGenerator<non-empty-string>
     */
    public static function domainNames(int $maximumLength = 253): ValueGenerator
    {
        if ($maximumLength < DomainNameGenerator::SHORTEST_LENGTH || $maximumLength > DomainNameGenerator::LONGEST_LENGTH) {
            throw new InvalidGeneratorArgumentException(
                sprintf(
                    'The maximum length of domain names (%d) must be from %d to %d',
                    $maximumLength,
                    DomainNameGenerator::SHORTEST_LENGTH,
                    DomainNameGenerator::LONGEST_LENGTH,
                ),
            );
        }

        return new DomainNameGenerator($maximumLength);
    }

    /**
     * Generates email addresses (RFC 5321) of at most 254 characters, with a
     * local part of at most 64 characters and a domain name from
     * domainNames().
     *
     * The local part consists of the characters that RFC 5322 allows in an
     * atom (letters, digits, and !#$%&'*+-/=?^_`{|}~) and of dots, but
     * neither starts nor ends with a dot nor has two dots in a row. These
     * email addresses pass filter_var() with FILTER_VALIDATE_EMAIL.
     *
     * With $quotedLocalParts, some local parts are quoted strings of
     * printable ASCII characters, including spaces, in which quotes and
     * backslashes are escaped with a backslash. With $ipAddressLiterals, some
     * domains are address literals of an IPv4 or an IPv6 address, for
     * instance [192.0.2.1] or [IPv6:2001:db8::1].
     *
     * Local parts of 64 characters and email addresses of 254 characters are
     * generated often. The simplest email address is a@a.aa. With
     * $ipAddressLiterals, the counterexample of a property whose outcome
     * does not depend on the domain can have the domain [0.0.0.0] or
     * [IPv6:::] instead, which take fewer choices than a.aa.
     *
     * @return ValueGenerator<non-empty-string>
     */
    public static function emailAddresses(bool $quotedLocalParts = false, bool $ipAddressLiterals = false): ValueGenerator
    {
        return new EmailAddressGenerator($quotedLocalParts, $ipAddressLiterals);
    }

    /**
     * Generates URLs (RFC 3986) of the schemes in $schemes with an authority:
     * a scheme, a host, an optional port, a path, an optional query, and an
     * optional fragment. With $userInformation, some URLs have user
     * information, a user name and an optional password, in front of the
     * host.
     *
     * The host is a domain name from domainNames(), an IPv4 address, or an
     * IPv6 address in brackets. The port is biased towards 1, 65535, and
     * common default ports. The path consists of segments, of which the last
     * one is empty for a trailing slash, and is empty often. The query
     * consists of key-value pairs, which are separated by "&".
     *
     * The URLs are normalized, as far as their syntax goes: the scheme and
     * the host are lowercase, characters are percent-encoded only when they
     * have to be, the hexadecimal digits of their encoding are uppercase, and
     * no segment is "." or "..". Reserved characters, spaces, and characters
     * that are encoded with two, three, and four bytes in UTF-8 are
     * percent-encoded often. These URLs pass filter_var() with
     * FILTER_VALIDATE_URL, except for some with user information: filter_var()
     * rejects a percent-encoded byte in the user information whose first
     * hexadecimal digit is a letter, such as %C3 of ä.
     *
     * The first scheme is the simplest one, and the simplest URL is
     * http://a.aa by default. An IP address takes fewer choices than a.aa,
     * though, so that the counterexample of a property whose outcome does
     * not depend on the host can have the host 0.0.0.0 or [::] instead.
     *
     * @param list<string> $schemes
     *
     * @throws InvalidGeneratorArgumentException
     *
     * @return ValueGenerator<non-empty-string>
     */
    public static function urls(array $schemes = ['http', 'https'], bool $userInformation = false): ValueGenerator
    {
        if ($schemes === []) {
            throw new InvalidGeneratorArgumentException('At least one scheme of URLs must be specified');
        }

        $valid = [];

        foreach ($schemes as $scheme) {
            if (preg_match('/^[a-z][a-z0-9+.-]*$/D', $scheme) !== 1) {
                throw new InvalidGeneratorArgumentException(
                    sprintf(
                        'The scheme of URLs (%s) must be a lowercase letter followed by lowercase letters, digits, "+", "-", and "."',
                        $scheme,
                    ),
                );
            }

            $valid[] = $scheme;
        }

        return new UrlGenerator(array_values(array_unique($valid)), $userInformation);
    }

    /**
     * Generates points in time from $minimum to $maximum as DateTimeImmutable
     * objects in one of the time zones in $timezones, which are given as
     * their identifier, their abbreviation, or their offset from UTC. By
     * default, the points in time are from 0001-01-01 00:00:00 UTC to
     * 9999-12-31 23:59:59 UTC, in UTC, and without microseconds.
     *
     * Half of the points in time are notable: points in time where 32-bit
     * timestamps overflow, the turn of the millennium, the first second of a
     * month or a year and the second before it, leap days, and the
     * transitions between standard time and daylight saving time of the time
     * zone, with the second before them. The other half are from the whole
     * range, biased towards the epoch, $minimum, and $maximum.
     *
     * The first time zone is the simplest one, and the simplest point in
     * time is the epoch, 1970-01-01 00:00:00 UTC, or the bound of the range
     * that is closest to it.
     *
     * @param list<string> $timezones
     *
     * @throws InvalidGeneratorArgumentException
     *
     * @return ValueGenerator<DateTimeImmutable>
     */
    public static function dateTimes(?DateTimeInterface $minimum = null, ?DateTimeInterface $maximum = null, array $timezones = ['UTC'], bool $microseconds = false): ValueGenerator
    {
        if ($minimum === null) {
            $minimum = new DateTimeImmutable('0001-01-01 00:00:00 UTC');
        }

        if ($maximum === null) {
            $maximum = new DateTimeImmutable('9999-12-31 23:59:59.999999 UTC');
        }

        if ($minimum > $maximum) {
            throw new InvalidGeneratorArgumentException(
                sprintf(
                    'The minimum (%s) must not be greater than the maximum (%s)',
                    $minimum->format(DateTimeInterface::RFC3339_EXTENDED),
                    $maximum->format(DateTimeInterface::RFC3339_EXTENDED),
                ),
            );
        }

        if ($timezones === []) {
            throw new InvalidGeneratorArgumentException('At least one time zone must be specified');
        }

        $valid = [];

        foreach (array_values(array_unique($timezones)) as $timezone) {
            try {
                $valid[] = new DateTimeZone($timezone);
            } catch (Exception) {
                throw new InvalidGeneratorArgumentException(
                    sprintf(
                        'The time zone (%s) must be an identifier, an abbreviation, or an offset from UTC',
                        $timezone,
                    ),
                );
            }
        }

        $lowest  = [$minimum->getTimestamp(), (int) $minimum->format('u')];
        $highest = [$maximum->getTimestamp(), (int) $maximum->format('u')];

        // without microseconds, the range is narrowed to whole seconds
        if (!$microseconds) {
            if ($lowest[1] > 0) {
                $lowest = [$lowest[0] + 1, 0];
            }

            $highest = [$highest[0], 0];

            if ($lowest[0] > $highest[0]) {
                throw new InvalidGeneratorArgumentException(
                    sprintf(
                        'The range from the minimum (%s) to the maximum (%s) must contain a whole second, use $microseconds to generate points in time with microseconds',
                        $minimum->format(DateTimeInterface::RFC3339_EXTENDED),
                        $maximum->format(DateTimeInterface::RFC3339_EXTENDED),
                    ),
                );
            }
        }

        assert($lowest[1] >= 0 && $lowest[1] <= 999999 && $highest[1] >= 0 && $highest[1] <= 999999);

        return new DateTimeGenerator($lowest, $highest, $valid, $microseconds);
    }

    /**
     * Generates the identifiers of time zones, such as Europe/Berlin. Half of
     * them are from time zones whose offsets or transitions between standard
     * time and daylight saving time are notable, such as Asia/Kathmandu
     * (UTC+05:45), Pacific/Kiritimati (UTC+14:00), Australia/Lord_Howe
     * (daylight saving time of 30 minutes), Europe/Dublin (negative daylight
     * saving time), or Pacific/Apia (which skipped a day), the other half
     * are from all time zones that DateTimeZone::listIdentifiers() lists.
     *
     * The simplest time zone is UTC.
     *
     * @return ValueGenerator<string>
     */
    public static function timezones(): ValueGenerator
    {
        return self::oneOf(
            self::elements(...self::NOTABLE_TIMEZONES),
            self::elements(...DateTimeZone::listIdentifiers()),
        );
    }

    /**
     * @param non-empty-string $function the name of the function that the closure that returned $generator was passed to
     *
     * @throws InvalidGeneratorArgumentException
     * @throws ValueGeneratorNotSupportedException
     *
     * @return AbstractGenerator<mixed>
     */
    private static function generatorReturnedBy(mixed $generator, string $function): AbstractGenerator
    {
        if (!$generator instanceof ValueGenerator) {
            throw new InvalidGeneratorArgumentException(
                sprintf(
                    'The closure passed to %s() must return a %s, %s was returned',
                    $function,
                    ValueGenerator::class,
                    get_debug_type($generator),
                ),
            );
        }

        return AbstractGenerator::from($generator);
    }

    private static function nextFloatThatIsNotSubnormal(float $subnormal, float $positive, float $negative): float
    {
        if ($subnormal > 0.0) {
            return $positive;
        }

        return $negative;
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
