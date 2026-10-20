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

use const INF;
use const NAN;
use const PHP_FLOAT_MAX;
use const PHP_INT_MAX;
use const PHP_INT_MIN;
use function abs;
use function array_filter;
use function array_keys;
use function array_map;
use function array_unique;
use function array_values;
use function count;
use function in_array;
use function is_array;
use function is_infinite;
use function is_nan;
use function max;
use function mb_check_encoding;
use function mb_str_split;
use function mb_strlen;
use function preg_match;
use function serialize;
use function strlen;
use Closure;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorRegistry;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ArrayGenerator;
use PHPUnit\Framework\PropertyBased\Generator\BooleanGenerator;
use PHPUnit\Framework\PropertyBased\Generator\BytesGenerator;
use PHPUnit\Framework\PropertyBased\Generator\CodePoints;
use PHPUnit\Framework\PropertyBased\Generator\ConstantGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ElementsGenerator;
use PHPUnit\Framework\PropertyBased\Generator\FilteredGenerator;
use PHPUnit\Framework\PropertyBased\Generator\FlatMappedGenerator;
use PHPUnit\Framework\PropertyBased\Generator\FloatGenerator;
use PHPUnit\Framework\PropertyBased\Generator\IntegerGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ListGenerator;
use PHPUnit\Framework\PropertyBased\Generator\MappedGenerator;
use PHPUnit\Framework\PropertyBased\Generator\NullableGenerator;
use PHPUnit\Framework\PropertyBased\Generator\OneOfGenerator;
use PHPUnit\Framework\PropertyBased\Generator\RecordGenerator;
use PHPUnit\Framework\PropertyBased\Generator\Repetition;
use PHPUnit\Framework\PropertyBased\Generator\StringGenerator;
use PHPUnit\Framework\PropertyBased\Generator\TupleGenerator;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\Currency;
use PHPUnit\TestFixture\PropertyBased\Derivation\Money;
use PHPUnit\TestFixture\PropertyBased\Derivation\Tree;
use PHPUnit\TestFixture\PropertyBased\EnumerationWithoutCases;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;
use stdClass;

#[CoversClass(Generators::class)]
#[CoversClass(AbstractGenerator::class)]
#[CoversClass(ArrayGenerator::class)]
#[CoversClass(BooleanGenerator::class)]
#[CoversClass(BytesGenerator::class)]
#[CoversClass(ConstantGenerator::class)]
#[CoversClass(ElementsGenerator::class)]
#[CoversClass(FilteredGenerator::class)]
#[CoversClass(FlatMappedGenerator::class)]
#[CoversClass(FloatGenerator::class)]
#[CoversClass(IntegerGenerator::class)]
#[CoversClass(ListGenerator::class)]
#[CoversClass(MappedGenerator::class)]
#[CoversClass(NullableGenerator::class)]
#[CoversClass(OneOfGenerator::class)]
#[CoversClass(RecordGenerator::class)]
#[CoversClass(Repetition::class)]
#[CoversClass(StringGenerator::class)]
#[CoversClass(TupleGenerator::class)]
#[CoversClass(ExampleRejectedException::class)]
#[CoversClass(InvalidGeneratorArgumentException::class)]
#[CoversClass(ValueGeneratorNotSupportedException::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class GeneratorsTest extends TestCase
{
    /**
     * @return array<string, array{ValueGenerator<mixed>}>
     */
    public static function generatorProvider(): array
    {
        return [
            'integers'           => [Generators::integers()],
            'bounded integers'   => [Generators::integers(-10, 10)],
            'floats'             => [Generators::floats(allowInfinity: true, allowNan: true)],
            'bounded floats'     => [Generators::floats(-1.5, 2.5)],
            'booleans'           => [Generators::booleans()],
            'strings'            => [Generators::strings()],
            'strings (alphabet)' => [Generators::strings('01', 1, 8)],
            'bytes'              => [Generators::bytes()],
            'regular expression' => [Generators::fromRegularExpression('/^[a-z]{1,3}(-\d+|\.[^a-z])?$/u')],
            'elements'           => [Generators::elements('a', 'b', 'c')],
            'cases'              => [Generators::cases(Currency::class)],
            'constant'           => [Generators::constant('constant')],
            'lists'              => [Generators::lists(Generators::integers(0, 100))],
            'lists (unique)'     => [Generators::lists(Generators::integers(0, 9), unique: true)],
            'arrays'             => [Generators::arrays(Generators::strings('abc', 1, 2), Generators::booleans())],
            'tuples'             => [Generators::tuples(Generators::integers(), Generators::strings())],
            'records'            => [Generators::records(['name' => Generators::strings(), 'age' => Generators::integers(0, 150)])],
            'records (optional)' => [Generators::records(['name' => Generators::strings(), 'age' => Generators::integers(0, 150)], ['age'])],
            'nullable'           => [Generators::nullable(Generators::integers())],
            'one of'             => [Generators::oneOf(Generators::integers(), Generators::booleans())],
            'frequency'          => [Generators::frequency([3, Generators::integers()], [1, Generators::booleans()])],
            'deferred'           => [Generators::deferred(static fn (): ValueGenerator => Generators::integers(0, 9))],
            'recursive'          => [Generators::recursive(Generators::integers(0, 9), static fn (ValueGenerator $values): ValueGenerator => Generators::lists($values, 0, 3))],
            'map'                => [Generators::integers(0, 9)->map(static fn (int $i): string => (string) $i)],
            'filter'             => [Generators::integers(0, 9)->filter(static fn (int $i): bool => $i % 2 === 0)],
            'flatMap'            => [Generators::integers(0, 3)->flatMap(static fn (int $n): ValueGenerator => Generators::lists(Generators::booleans(), $n, $n))],
        ];
    }

    /**
     * @return array<string, array{int, int}>
     */
    public static function integerRangeProvider(): array
    {
        return [
            'full range'                => [PHP_INT_MIN, PHP_INT_MAX],
            'non-negative'              => [0, 10],
            'positive'                  => [5, 10],
            'negative'                  => [-10, -1],
            'negative and non-negative' => [-5, 5],
            'single value'              => [7, 7],
            'around PHP_INT_MIN'        => [PHP_INT_MIN, PHP_INT_MIN + 3],
            'around PHP_INT_MAX'        => [PHP_INT_MAX - 3, PHP_INT_MAX],
            'without PHP_INT_MIN'       => [-9223372036854775807, PHP_INT_MAX],
        ];
    }

    /**
     * @return array<string, array{int, int, int}>
     */
    public static function simplestIntegerProvider(): array
    {
        return [
            'negative and non-negative' => [0, PHP_INT_MIN, PHP_INT_MAX],
            'positive'                  => [5, 5, 10],
            'negative'                  => [-5, -10, -5],
        ];
    }

    /**
     * @return array<string, array{float, float}>
     */
    public static function floatRangeProvider(): array
    {
        return [
            'full range'                => [-PHP_FLOAT_MAX, PHP_FLOAT_MAX],
            'unit interval'             => [0.0, 1.0],
            'positive'                  => [5.0, 10.0],
            'negative'                  => [-10.0, -5.0],
            'negative and non-negative' => [-1.5, 2.5],
            'single value'              => [3.25, 3.25],
            'large magnitudes'          => [1.0E300, 1.0E301],
        ];
    }

    /**
     * @return array<string, array{string, list<int>, float, float}>
     */
    public static function simplestFloatProvider(): array
    {
        return [
            'zero'                        => ['0', [], -PHP_FLOAT_MAX, PHP_FLOAT_MAX],
            'negative zero'               => ['-0', [1], -PHP_FLOAT_MAX, PHP_FLOAT_MAX],
            'small integer'               => ['3', [0, 0, 3], -PHP_FLOAT_MAX, PHP_FLOAT_MAX],
            'minimum of a positive range' => ['5', [], 5.0, 10.0],
            'maximum of a negative range' => ['-5', [], -10.0, -5.0],
        ];
    }

    /**
     * @return array<string, array{string, float}>
     */
    public static function valueThatIsNotAllowedProvider(): array
    {
        return [
            'NaN'               => ['0', NAN],
            'infinity'          => [(string) PHP_FLOAT_MAX, INF],
            'negative infinity' => [(string) -PHP_FLOAT_MAX, -INF],
        ];
    }

    /**
     * @return array<string, array{float, float, string}>
     */
    public static function invalidFloatRangeProvider(): array
    {
        return [
            'minimum greater than maximum' => [2.0, 1.0, 'The minimum (2.000000) must not be greater than the maximum (1.000000)'],
            'infinite minimum'             => [-INF, 1.0, 'The minimum and the maximum must be finite, use $allowInfinity to generate infinite values'],
            'NaN maximum'                  => [0.0, NAN, 'The minimum and the maximum must be finite, use $allowInfinity to generate infinite values'],
        ];
    }

    /**
     * @return array<string, array{?string, int, ?int, string}>
     */
    public static function invalidStringArgumentsProvider(): array
    {
        return [
            'empty alphabet'                   => ['', 0, null, 'The alphabet must be a non-empty, valid UTF-8 string'],
            'invalid UTF-8 in alphabet'        => ["\xFF", 0, null, 'The alphabet must be a non-empty, valid UTF-8 string'],
            'negative minimum length'          => [null, -1, null, 'The minimum length (-1) must not be negative'],
            'maximum length less than minimum' => [null, 3, 2, 'The maximum length (2) must not be smaller than the minimum length (3)'],
        ];
    }

    /**
     * @return array<string, array{list<mixed>, string}>
     */
    public static function invalidFrequencyArgumentsProvider(): array
    {
        return [
            'no weighted generator'   => [[], 'At least one weighted generator must be passed to frequency()'],
            'weight that is not int'  => [[['1', Generators::booleans()]], 'Argument #1 of frequency() must be a list of a weight and a generator'],
            'no generator'            => [[[1, Generators::booleans()], [1, true]], 'Argument #2 of frequency() must be a list of a weight and a generator'],
            'more than two elements'  => [[[1, Generators::booleans(), 'extra']], 'Argument #1 of frequency() must be a list of a weight and a generator'],
            'weight that is zero'     => [[[0, Generators::booleans()]], 'The weight in argument #1 of frequency() (0) must be positive'],
            'weight that is negative' => [[[1, Generators::booleans()], [-1, Generators::booleans()]], 'The weight in argument #2 of frequency() (-1) must be positive'],
        ];
    }

    #[DataProvider('valueThatIsNotAllowedProvider')]
    public function testFloatsThatAreNotAllowedAreMappedIntoTheRange(string $expected, float $value): void
    {
        $value = $this->replay(Generators::floats(), FloatGenerator::encode($value));

        $this->assertIsFloat($value);
        $this->assertSame($expected, (string) $value);
    }

    public function testDoesNotGeneratePositiveZeroForARangeThatEndsAtNegativeZero(): void
    {
        $value = $this->replay(Generators::floats(-1.0, -0.0), []);

        $this->assertIsFloat($value);
        $this->assertSame('-0', (string) $value);
    }

    /**
     * @param ValueGenerator<mixed> $generator
     */
    #[DataProvider('generatorProvider')]
    public function testReplayingTheChoiceSequenceOfAValueYieldsTheSameValue(ValueGenerator $generator): void
    {
        $randomizer = new Randomizer(new Xoshiro256StarStar(1));

        for ($i = 0; $i < 200; $i++) {
            $source = ChoiceSource::random($randomizer);

            try {
                $value = AbstractGenerator::from($generator)->generate($source);
            } catch (ExampleRejectedException) {
                continue;
            }

            $this->assertSame(
                serialize($value),
                serialize($this->replay($generator, $source->sequence()->choices())),
            );
        }
    }

    /**
     * @param ValueGenerator<mixed> $generator
     */
    #[DataProvider('generatorProvider')]
    public function testGeneratesTheSameValuesForTheSameSeed(ValueGenerator $generator): void
    {
        $this->assertSame(
            serialize($this->values($generator, 50, 42)),
            serialize($this->values($generator, 50, 42)),
        );
    }

    #[DataProvider('integerRangeProvider')]
    public function testGeneratesIntegersWithinTheRange(int $minimum, int $maximum): void
    {
        foreach ($this->values(Generators::integers($minimum, $maximum), 500) as $value) {
            $this->assertIsInt($value);
            $this->assertGreaterThanOrEqual($minimum, $value);
            $this->assertLessThanOrEqual($maximum, $value);
        }
    }

    public function testGeneratesIntegersOfBothSignsAndBothParities(): void
    {
        $values = $this->values(Generators::integers(), 1000);

        $this->assertNotEmpty(array_filter($values, static fn (int $value): bool => $value < 0));
        $this->assertNotEmpty(array_filter($values, static fn (int $value): bool => $value >= 0));
        $this->assertNotEmpty(array_filter($values, static fn (int $value): bool => $value % 2 === 0));
        $this->assertNotEmpty(array_filter($values, static fn (int $value): bool => $value % 2 !== 0));
    }

    public function testGeneratesTheBoundariesAndTheIntegersAroundZero(): void
    {
        $values = $this->values(Generators::integers(), 1000);

        $this->assertContains(0, $values);
        $this->assertContains(1, $values);
        $this->assertContains(-1, $values);
        $this->assertContains(PHP_INT_MIN, $values);
        $this->assertContains(PHP_INT_MAX, $values);
    }

    #[DataProvider('simplestIntegerProvider')]
    public function testTheSimplestIntegerIsTheOneClosestToZero(int $expected, int $minimum, int $maximum): void
    {
        $this->assertSame($expected, $this->replay(Generators::integers($minimum, $maximum), []));
    }

    public function testTheChoiceForAnIntegerInTheNegativeHalfOfTheRangeStandsForItsBitwiseComplement(): void
    {
        $generator = Generators::integers();

        $this->assertSame(-1, $this->replay($generator, [1, 0]));
        $this->assertSame(-5, $this->replay($generator, [1, 4]));
        $this->assertSame(PHP_INT_MIN, $this->replay($generator, [1, PHP_INT_MAX]));
        $this->assertSame(PHP_INT_MAX, $this->replay($generator, [0, PHP_INT_MAX]));
    }

    public function testTheMinimumOfAnIntegerRangeMustNotBeGreaterThanItsMaximum(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The minimum (2) must not be greater than the maximum (1)');

        Generators::integers(2, 1);
    }

    #[DataProvider('floatRangeProvider')]
    public function testGeneratesFiniteFloatsWithinTheRange(float $minimum, float $maximum): void
    {
        foreach ($this->values(Generators::floats($minimum, $maximum), 500) as $value) {
            $this->assertIsFloat($value);
            $this->assertFalse(is_nan($value));
            $this->assertFalse(is_infinite($value));
            $this->assertGreaterThanOrEqual($minimum, $value);
            $this->assertLessThanOrEqual($maximum, $value);
        }
    }

    public function testGeneratesSpecialFloats(): void
    {
        $values = array_map(static fn (float $value): string => serialize($value), $this->values(Generators::floats(), 2000));

        $this->assertContains(serialize(0.0), $values);
        $this->assertContains(serialize(-0.0), $values);
        $this->assertContains(serialize(PHP_FLOAT_MAX), $values);
        $this->assertContains(serialize(4.9E-324), $values);
        $this->assertContains(serialize(9.2233720368547758E+18), $values);
    }

    public function testGeneratesFloatsOfVerySmallAndVeryLargeMagnitudes(): void
    {
        $values = $this->values(Generators::floats(), 1000);

        $this->assertNotEmpty(array_filter($values, static fn (float $value): bool => $value !== 0.0 && abs($value) < 1.0E-100));
        $this->assertNotEmpty(array_filter($values, static fn (float $value): bool => abs($value) > 1.0E100));
    }

    public function testGeneratesInfiniteFloatsAndNanOnlyWhenAllowed(): void
    {
        $values = $this->values(Generators::floats(), 2000);

        $this->assertEmpty(array_filter($values, static fn (float $value): bool => is_nan($value) || is_infinite($value)));

        $values = $this->values(Generators::floats(allowInfinity: true, allowNan: true), 2000);

        $this->assertContains(INF, $values);
        $this->assertContains(-INF, $values);
        $this->assertNotEmpty(array_filter($values, static fn (float $value): bool => is_nan($value)));
    }

    public function testGeneratesInfinityOnlyInTheDirectionInWhichTheRangeIsUnbounded(): void
    {
        $values = $this->values(Generators::floats(0.0, PHP_FLOAT_MAX, allowInfinity: true), 2000);

        $this->assertContains(INF, $values);
        $this->assertNotContains(-INF, $values);
    }

    public function testDoesNotGenerateNegativeZeroForARangeThatStartsAtZero(): void
    {
        foreach ($this->values(Generators::floats(0.0, 1.0), 2000) as $value) {
            $this->assertNotSame('-0', (string) $value);
        }
    }

    /**
     * @param list<non-negative-int> $choices
     */
    #[DataProvider('simplestFloatProvider')]
    public function testFloatsAreReplayedFromTheirChoices(string $expected, array $choices, float $minimum, float $maximum): void
    {
        $value = $this->replay(Generators::floats($minimum, $maximum), $choices);

        $this->assertIsFloat($value);
        $this->assertSame($expected, (string) $value);
    }

    #[DataProvider('invalidFloatRangeProvider')]
    public function testTheRangeOfFloatsMustBeValid(float $minimum, float $maximum, string $message): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage($message);

        Generators::floats($minimum, $maximum);
    }

    public function testGeneratesBothBooleans(): void
    {
        $values = $this->values(Generators::booleans(), 100);

        $this->assertContains(true, $values);
        $this->assertContains(false, $values);
        $this->assertFalse($this->replay(Generators::booleans(), []));
    }

    public function testGeneratesValidUtf8StringsWithinTheLengthInCharacters(): void
    {
        foreach ($this->values(Generators::strings(minimumLength: 2, maximumLength: 5), 500) as $value) {
            $this->assertIsString($value);
            $this->assertTrue(mb_check_encoding($value, 'UTF-8'));
            $this->assertGreaterThanOrEqual(2, mb_strlen($value, 'UTF-8'));
            $this->assertLessThanOrEqual(5, mb_strlen($value, 'UTF-8'));
        }
    }

    public function testGeneratesEmptyAsciiMultibyteCombiningAndEmojiStrings(): void
    {
        $values = $this->values(Generators::strings(), 1000);

        $this->assertContains('', $values);
        $this->assertNotEmpty(array_filter($values, static fn (string $value): bool => preg_match('/^[a-zA-Z0-9]+$/', $value) === 1));
        $this->assertNotEmpty(array_filter($values, static fn (string $value): bool => strlen($value) !== mb_strlen($value, 'UTF-8')));
        $this->assertNotEmpty(array_filter($values, static fn (string $value): bool => preg_match('/\p{Mn}/u', $value) === 1));
        $this->assertNotEmpty(array_filter($values, static fn (string $value): bool => preg_match('/[\x{1F300}-\x{1FAFF}]/u', $value) === 1));
    }

    public function testGeneratesStringsFromTheCharactersOfTheAlphabet(): void
    {
        foreach ($this->values(Generators::strings('äb€'), 200) as $value) {
            $this->assertIsString($value);

            foreach (mb_str_split($value, 1, 'UTF-8') as $character) {
                $this->assertContains($character, ['ä', 'b', '€']);
            }
        }
    }

    public function testTheSimplestStringConsistsOfTheSimplestCharacters(): void
    {
        $this->assertSame('', $this->replay(Generators::strings(), []));
        $this->assertSame('aa', $this->replay(Generators::strings(minimumLength: 2), []));
        $this->assertSame('11', $this->replay(Generators::strings('10', 2), []));
    }

    public function testTheCharacterOfAStringIsReplayedFromItsIndexInTheOrderOfCodePoints(): void
    {
        $this->assertSame('€', $this->replay(Generators::strings(minimumLength: 1, maximumLength: 1), [CodePoints::index(0x20AC)]));
    }

    #[DataProvider('invalidStringArgumentsProvider')]
    public function testTheArgumentsForStringsMustBeValid(?string $alphabet, int $minimumLength, ?int $maximumLength, string $message): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage($message);

        Generators::strings($alphabet, $minimumLength, $maximumLength);
    }

    public function testGeneratesBinaryStringsWithinTheLengthInBytes(): void
    {
        $values = $this->values(Generators::bytes(1, 4), 500);

        foreach ($values as $value) {
            $this->assertIsString($value);
            $this->assertGreaterThanOrEqual(1, strlen($value));
            $this->assertLessThanOrEqual(4, strlen($value));
        }

        $this->assertNotEmpty(array_filter($values, static fn (string $value): bool => !mb_check_encoding($value, 'UTF-8')));
        $this->assertSame("\x00", $this->replay(Generators::bytes(1), []));
    }

    public function testTheLengthOfBinaryStringsMustBeValid(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The minimum length (-1) must not be negative');

        Generators::bytes(-1);
    }

    public function testGeneratesTheGivenElements(): void
    {
        $generator = Generators::elements('a', 'b', 'c');

        $this->assertArraysHaveIdenticalValuesIgnoringOrder(['a', 'b', 'c'], $this->distinct($this->values($generator, 100)));
        $this->assertSame('a', $this->replay($generator, []));
        $this->assertSame('b', $this->replay($generator, [1]));
    }

    public function testAtLeastOneElementMustBeGiven(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('At least one value must be passed to elements()');

        Generators::elements();
    }

    public function testGeneratesTheCasesOfAnEnumeration(): void
    {
        $generator = Generators::cases(Currency::class);

        $this->assertArraysHaveIdenticalValuesIgnoringOrder(Currency::cases(), $this->distinct($this->values($generator, 100)));
        $this->assertSame(Currency::EUR, $this->replay($generator, []));
    }

    public function testCasesCanOnlyBeGeneratedForAnEnumeration(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('stdClass is not an enumeration');

        Generators::cases('stdClass');
    }

    public function testCasesCanOnlyBeGeneratedForAnEnumerationThatHasCases(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('Enumeration ' . EnumerationWithoutCases::class . ' has no cases');

        Generators::cases(EnumerationWithoutCases::class);
    }

    public function testGeneratesAConstantWithoutDrawingAChoice(): void
    {
        $source = ChoiceSource::random(new Randomizer(new Xoshiro256StarStar(1)));

        $this->assertSame('constant', AbstractGenerator::from(Generators::constant('constant'))->generate($source));
        $this->assertSame([], $source->sequence()->choices());
    }

    public function testGeneratesListsWithinTheSize(): void
    {
        foreach ($this->values(Generators::lists(Generators::integers(0, 9), 2, 4), 500) as $value) {
            $this->assertIsList($value);
            $this->assertGreaterThanOrEqual(2, count($value));
            $this->assertLessThanOrEqual(4, count($value));
        }
    }

    public function testGeneratesEmptyAndLongLists(): void
    {
        $sizes = array_map(count(...), $this->values(Generators::lists(Generators::integers()), 500));

        $this->assertContains(0, $sizes);
        $this->assertNotEmpty(array_filter($sizes, static fn (int $size): bool => $size >= 10));
    }

    public function testTheSimplestListHasTheMinimumSizeAndConsistsOfTheSimplestElements(): void
    {
        $this->assertSame([], $this->replay(Generators::lists(Generators::integers()), []));
        $this->assertSame([0, 0], $this->replay(Generators::lists(Generators::integers(), 2), []));
    }

    public function testEachElementOfAListIsPrecededByTheChoiceToAddItAndMarkedAsASpan(): void
    {
        $generator = AbstractGenerator::from(Generators::lists(Generators::integers(0, 9)));
        $source    = ChoiceSource::replay(new ChoiceSequence([1, 5, 1, 7, 0]));

        $this->assertSame([5, 7], $generator->generate($source));

        $spans = array_map(
            static fn (Span $span): array => [$span->start(), $span->end()],
            $source->sequence()->spans(),
        );

        $this->assertContains([0, 2], $spans);
        $this->assertContains([2, 4], $spans);
    }

    public function testTheSpansOfGeneratorsThatAreComposedAndConfiguredTheSameWayHaveTheSameLabel(): void
    {
        $this->assertSame(
            AbstractGenerator::from(Generators::lists(Generators::integers(0, 9)))->label(),
            AbstractGenerator::from(Generators::lists(Generators::integers(0, 9)))->label(),
        );

        $this->assertNotSame(
            AbstractGenerator::from(Generators::lists(Generators::integers(0, 9)))->label(),
            AbstractGenerator::from(Generators::lists(Generators::integers(0, 8)))->label(),
        );

        $this->assertNotSame(
            AbstractGenerator::from(Generators::lists(Generators::integers(0, 9)))->label(),
            AbstractGenerator::from(Generators::lists(Generators::strings()))->label(),
        );
    }

    public function testTheSpansOfTheLevelsOfARecursiveValueHaveTheSameLabel(): void
    {
        $generator = AbstractGenerator::from(
            Generators::recursive(
                Generators::integers(0, 9),
                static fn (ValueGenerator $values): ValueGenerator => Generators::lists($values),
            ),
        );

        $source = ChoiceSource::replay(new ChoiceSequence([1, 1, 1, 1, 0, 5, 0, 0]));

        $this->assertSame([[5]], $generator->generate($source));

        $spansOfLevels = array_values(
            array_filter(
                $source->sequence()->spans(),
                static fn (Span $span): bool => $span->label() === $generator->label(),
            ),
        );

        $this->assertSame(
            [[0, 8], [2, 7], [4, 6]],
            array_map(static fn (Span $span): array => [$span->start(), $span->end()], $spansOfLevels),
        );
    }

    public function testDeletingTheSpanOfAnElementFromTheChoiceSequenceDeletesTheElement(): void
    {
        $this->assertSame([7], $this->replay(Generators::lists(Generators::integers(0, 9)), [1, 7, 0]));
    }

    public function testAddsAnotherElementToARecursiveListLessOften(): void
    {
        $this->assertLessThan(
            $this->numberOfNonEmptyArrays($this->values(Generators::lists(Generators::integers()), 500)) / 2,
            $this->numberOfNonEmptyArrays($this->values(Generators::lists(Generators::integers(), recursive: true), 500)),
        );
    }

    public function testGeneratesListsOfUniqueElements(): void
    {
        foreach ($this->values(Generators::lists(Generators::integers(0, 9), 0, 8, unique: true), 500) as $value) {
            $this->assertIsList($value);
            $this->assertSame($value, array_values(array_unique($value)));
        }
    }

    public function testGeneratesListsOfUniqueObjectsThatAreNotEqual(): void
    {
        $objects = Generators::integers(0, 3)->map(static fn (int $value): stdClass => (object) ['value' => $value]);

        foreach ($this->values(Generators::lists($objects, unique: true), 500) as $value) {
            $this->assertIsList($value);
            $this->assertSame(count($value), count(array_unique(array_map(static fn (stdClass $object): mixed => $object->value, $value))));
        }
    }

    public function testAnElementThatEqualsAnEarlierElementOfAListOfUniqueElementsIsGeneratedAgain(): void
    {
        $this->assertSame([3, 4], $this->replay(Generators::lists(Generators::integers(0, 9), unique: true), [1, 3, 1, 3, 1, 4, 0]));
    }

    public function testRejectsTheExampleWhenNotEnoughUniqueElementsAreGenerated(): void
    {
        $this->expectException(ExampleRejectedException::class);
        $this->expectExceptionMessage('No new element for the collection was generated in 10 attempts');

        $this->replay(Generators::lists(Generators::elements('a', 'b'), 3, unique: true), []);
    }

    public function testTheSizeOfListsMustBeValid(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The maximum size (1) must not be smaller than the minimum size (2)');

        Generators::lists(Generators::integers(), 2, 1);
    }

    public function testGeneratesArraysWithUniqueKeysWithinTheSize(): void
    {
        foreach ($this->values(Generators::arrays(Generators::integers(0, 20), Generators::booleans(), 1, 5), 500) as $value) {
            $this->assertIsArray($value);
            $this->assertGreaterThanOrEqual(1, count($value));
            $this->assertLessThanOrEqual(5, count($value));
            $this->assertContainsOnlyBool($value);
        }
    }

    public function testGeneratesArraysWithAllKeysWhenTheMinimumSizeRequiresIt(): void
    {
        $value = $this->replay(Generators::arrays(Generators::elements('a', 'b'), Generators::booleans(), 2), [0, 0, 1, 0]);

        $this->assertIsArray($value);
        $this->assertArraysHaveIdenticalValuesIgnoringOrder(['a', 'b'], array_keys($value));
    }

    public function testRejectsTheExampleWhenTooManyDuplicateKeysAreGenerated(): void
    {
        $this->expectException(ExampleRejectedException::class);
        $this->expectExceptionMessage('No new element for the collection was generated in 10 attempts');

        $this->replay(Generators::arrays(Generators::elements('a', 'b'), Generators::booleans(), 3), []);
    }

    public function testStopsAddingElementsWhenTooManyDuplicateKeysAreGeneratedAfterTheMinimumSizeIsReached(): void
    {
        // the first element is added, then each of the following ten
        // elements duplicates its key: 1 to add an element, 0 for the key
        // of the only element, and 0 for false as the value of the first one
        $choices = [1, 0, 0];

        for ($i = 0; $i < 10; $i++) {
            $choices[] = 1;
            $choices[] = 0;
        }

        $this->assertSame(['a' => false], $this->replay(Generators::arrays(Generators::elements('a'), Generators::booleans()), $choices));
    }

    public function testAddsAnotherElementToARecursiveArrayLessOften(): void
    {
        $this->assertLessThan(
            $this->numberOfNonEmptyArrays($this->values(Generators::arrays(Generators::integers(), Generators::integers()), 500)) / 2,
            $this->numberOfNonEmptyArrays($this->values(Generators::arrays(Generators::integers(), Generators::integers(), recursive: true), 500)),
        );
    }

    public function testDoesNotUseStringKeysThatAreStoredAsIntegers(): void
    {
        foreach ($this->values(Generators::arrays(Generators::elements('0', '1', 'a', '01'), Generators::booleans()), 500) as $value) {
            $this->assertIsArray($value);

            foreach (array_keys($value) as $key) {
                $this->assertIsString($key);
            }
        }
    }

    public function testTheKeysOfArraysMustBeIntegersOrStrings(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The keys of an array must be integers or strings, the generator for the keys generated float');

        $this->replay(Generators::arrays(Generators::floats(), Generators::booleans(), 1), []);
    }

    public function testTheSizeOfArraysMustBeValid(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The minimum size (-1) must not be negative');

        Generators::arrays(Generators::integers(), Generators::integers(), -1);
    }

    public function testGeneratesTuplesWithOneValueOfEachGenerator(): void
    {
        $this->assertSame([0, false, 'a'], $this->replay(Generators::tuples(Generators::integers(), Generators::booleans(), Generators::constant('a')), []));
        $this->assertSame([], $this->replay(Generators::tuples(), []));
    }

    public function testGeneratesRecordsWithAValueOfTheRespectiveGeneratorForEachKey(): void
    {
        $this->assertSame(
            ['name' => '', 'age' => 0],
            $this->replay(Generators::records(['name' => Generators::strings(), 'age' => Generators::integers(0, 150)]), []),
        );
    }

    public function testOptionalKeysOfRecordsAreOmittedOrAddedDependingOnTheirChoice(): void
    {
        $generator = Generators::records(['a' => Generators::integers(0, 9), 'b' => Generators::integers(0, 9)], ['b']);

        $this->assertSame(['a' => 0], $this->replay($generator, []));
        $this->assertSame(['a' => 3], $this->replay($generator, [3, 0]));
        $this->assertSame(['a' => 3, 'b' => 4], $this->replay($generator, [3, 1, 4]));
    }

    public function testANumericStringIsTheSameOptionalKeyAsTheIntegerThatPhpUsesAsTheKeyOfTheRecord(): void
    {
        $this->assertSame([], $this->replay(Generators::records([1 => Generators::integers(0, 9)], ['1']), [0]));
    }

    public function testTheOptionalKeysOfARecordMustBeKeysOfTheRecord(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage("The optional key 'c' must be one of the keys of the record");

        Generators::records(['a' => Generators::integers(), 'b' => Generators::integers()], ['b', 'c']);
    }

    public function testGeneratesNullOrAValueOfTheGenerator(): void
    {
        $generator = Generators::nullable(Generators::integers(1, 9));
        $values    = $this->values($generator, 100);

        $this->assertContains(null, $values);
        $this->assertNotEmpty(array_filter($values, static fn (?int $value): bool => $value !== null));
        $this->assertNull($this->replay($generator, []));
        $this->assertSame(1, $this->replay($generator, [1]));
    }

    public function testGeneratesAValueRatherThanNullLessOftenForARecursiveNullableValue(): void
    {
        $values = static fn (array $values): int => count(array_filter($values, static fn (?int $value): bool => $value !== null));

        $this->assertLessThan(
            $values($this->values(Generators::nullable(Generators::integers()), 500)) / 2,
            $values($this->values(Generators::nullable(Generators::integers(), recursive: true), 500)),
        );
    }

    public function testGeneratesObjectsOfARecursiveClassWithTheGeneratorThatIsRegisteredForIt(): void
    {
        $trees        = Generators::lists(Generators::objects(Tree::class), recursive: true)->map(static fn (array $children): Tree => new Tree($children));
        $deriver      = new GeneratorDeriver(GeneratorRegistry::from([Tree::class => $trees]));
        $withChildren = 0;

        foreach ($this->values($trees, 200, 1, $deriver) as $tree) {
            $this->assertInstanceOf(Tree::class, $tree);

            if ($tree->children() !== []) {
                $withChildren++;
            }
        }

        $this->assertGreaterThan(0, $withChildren);
        $this->assertEquals(new Tree([new Tree([])]), $this->replay($trees, [1, 0, 0], $deriver));
    }

    public function testGeneratesAValueOfOneOfTheGenerators(): void
    {
        $generator = Generators::oneOf(Generators::constant('a'), Generators::constant('b'));

        $this->assertArraysHaveIdenticalValuesIgnoringOrder(['a', 'b'], $this->distinct($this->values($generator, 100)));
        $this->assertSame('a', $this->replay($generator, []));
        $this->assertSame('b', $this->replay($generator, [1]));
    }

    public function testAtLeastOneGeneratorMustBeGivenToOneOf(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('At least one generator must be passed to oneOf()');

        Generators::oneOf();
    }

    public function testGeneratesTheValuesOfTheGeneratorsInProportionToTheirWeights(): void
    {
        $generator = Generators::frequency([9, Generators::constant('a')], [1, Generators::constant('b')]);
        $values    = $this->values($generator, 1000);
        $b         = count(array_filter($values, static fn (string $value): bool => $value === 'b'));

        $this->assertGreaterThan(50, $b);
        $this->assertLessThan(150, $b);
        $this->assertSame('a', $this->replay($generator, []));
        $this->assertSame('b', $this->replay($generator, [1]));
    }

    /**
     * @param list<mixed> $arguments
     */
    #[DataProvider('invalidFrequencyArgumentsProvider')]
    public function testTheArgumentsOfFrequencyMustBeWeightedGenerators(array $arguments, string $message): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage($message);

        Generators::frequency(...$arguments);
    }

    public function testGeneratesTheValuesOfTheGeneratorThatTheClosurePassedToDeferredReturns(): void
    {
        $called    = false;
        $generator = Generators::deferred(
            static function () use (&$called): ValueGenerator
            {
                $called = true;

                return Generators::integers(0, 9);
            },
        );

        $this->assertFalse($called);
        $this->assertSame(3, $this->replay($generator, [3]));
        $this->assertTrue($called);
    }

    public function testADeferredGeneratorCanReferToItself(): void
    {
        $values = Generators::deferred(
            static function () use (&$values): ValueGenerator
            {
                return Generators::oneOf(
                    Generators::integers(0, 9),
                    Generators::lists($values, recursive: true),
                );
            },
        );

        $nested = 0;

        foreach ($this->values($values, 200) as $value) {
            if ($this->depth($value) > 1) {
                $nested++;
            }
        }

        $this->assertGreaterThan(0, $nested);
        $this->assertSame(0, $this->replay($values, []));
        $this->assertSame([[7]], $this->replay($values, [1, 1, 1, 1, 0, 7, 0, 0]));
    }

    public function testTheClosurePassedToDeferredMustReturnAValueGenerator(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The closure passed to deferred() must return a PHPUnit\Framework\PropertyBased\ValueGenerator, int was returned');

        $this->replay(Generators::deferred(static fn (): int => 1), []);
    }

    public function testADeferredGeneratorThatRefersToItselfBeforeItMakesAChoiceIsReported(): void
    {
        $values = Generators::deferred(
            static function () use (&$values): ValueGenerator
            {
                return $values;
            },
        );

        $this->expectException(GeneratorRefersToItselfException::class);
        $this->expectExceptionMessage('Cannot generate the values of a generator that was created with Generators::deferred(): the generator refers to itself before it makes a choice');

        $this->replay($values, []);
    }

    public function testGeneratesValuesThatAreNestedUpToTheMaximumDepth(): void
    {
        $generator = Generators::recursive(
            Generators::integers(0, 9),
            static fn (ValueGenerator $values): ValueGenerator => Generators::lists($values, 1, 2),
            3,
        );

        $depths = array_map($this->depth(...), $this->values($generator, 500));

        $this->assertContains(0, $depths);
        $this->assertContains(3, $depths);
        $this->assertLessThanOrEqual(3, max($depths));
        $this->assertSame(0, $this->replay($generator, []));
        $this->assertSame([5], $this->replay($generator, [1, 0, 5, 0]));
    }

    public function testGeneratesOnlyTheValuesOfTheBaseGeneratorForAMaximumDepthOfZero(): void
    {
        $generator = Generators::recursive(
            Generators::integers(0, 9),
            static fn (ValueGenerator $values): ValueGenerator => Generators::lists($values),
            0,
        );

        foreach ($this->values($generator, 100) as $value) {
            $this->assertIsInt($value);
        }
    }

    public function testTheMaximumDepthOfRecursiveValuesMustNotBeNegative(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The maximum depth (-1) must not be negative');

        Generators::recursive(Generators::integers(), static fn (ValueGenerator $values): ValueGenerator => Generators::lists($values), -1);
    }

    public function testTheClosurePassedToRecursiveMustReturnAValueGenerator(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The closure passed to recursive() must return a PHPUnit\Framework\PropertyBased\ValueGenerator, null was returned');

        Generators::recursive(Generators::integers(), static fn (ValueGenerator $values): null => null);
    }

    public function testMapsTheValuesOfAGenerator(): void
    {
        $this->assertSame('3', $this->replay(Generators::integers(0, 9)->map(static fn (int $value): string => (string) $value), [3]));
    }

    public function testFiltersTheValuesOfAGenerator(): void
    {
        foreach ($this->values(Generators::integers(0, 9)->filter(static fn (int $value): bool => $value % 2 === 0), 200) as $value) {
            $this->assertIsInt($value);
            $this->assertSame(0, $value % 2);
        }
    }

    public function testValuesThatDoNotSatisfyTheFilterAreGeneratedAgain(): void
    {
        $this->assertSame(4, $this->replay(Generators::integers(0, 9)->filter(static fn (int $value): bool => $value % 2 === 0), [3, 5, 4]));
    }

    public function testRejectsTheExampleWhenTheFilterIsNotSatisfiedInThreeAttempts(): void
    {
        $this->expectException(ExampleRejectedException::class);
        $this->expectExceptionMessage('No value that satisfies the filter was generated in 3 attempts');

        $this->replay(Generators::integers()->filter(static fn (int $value): bool => false), []);
    }

    public function testTheClosurePassedToFilterMustReturnABool(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The closure passed to filter() must return a bool, int was returned');

        $this->replay(Generators::integers()->filter(static fn (int $value): int => 1), []);
    }

    public function testFlatMapsTheValuesOfAGenerator(): void
    {
        $generator = Generators::integers(1, 3)->flatMap(static fn (int $size): ValueGenerator => Generators::lists(Generators::constant('x'), $size, $size));

        foreach ($this->values($generator, 100) as $value) {
            $this->assertIsList($value);
            $this->assertGreaterThanOrEqual(1, count($value));
            $this->assertLessThanOrEqual(3, count($value));
        }

        $this->assertSame(['x', 'x'], $this->replay($generator, [1]));
    }

    public function testTheClosurePassedToFlatMapMustReturnAValueGenerator(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The closure passed to flatMap() must return a PHPUnit\Framework\PropertyBased\ValueGenerator, int was returned');

        $this->replay(Generators::integers()->flatMap(static fn (int $value): int => $value), []);
    }

    public function testGeneratorsThatWereNotCreatedByPhpunitAreNotSupported(): void
    {
        $this->expectException(ValueGeneratorNotSupportedException::class);
        $this->expectExceptionMessage('implements PHPUnit\Framework\PropertyBased\ValueGenerator, which is not supported');

        Generators::lists($this->foreignGenerator());
    }

    public function testGeneratorsThatWereNotCreatedByPhpunitAreNotSupportedInFlatMap(): void
    {
        $foreignGenerator = $this->foreignGenerator();

        $this->expectException(ValueGeneratorNotSupportedException::class);

        $this->replay(Generators::integers()->flatMap(static fn (int $value): ValueGenerator => $foreignGenerator), []);
    }

    public function testGeneratesObjectsOfAClass(): void
    {
        $this->assertEquals(new Money(0, Currency::EUR), $this->replay(Generators::objects(Money::class), []));
    }

    public function testObjectsCanOnlyBeGeneratedForAClassOrAnInterface(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('Unknown is neither a class nor an interface');

        Generators::objects('Unknown');
    }

    public function testGeneratesValuesOfATypeWhoseClassNamesAreResolvedWhereFromTypeIsCalled(): void
    {
        $this->assertSame([Currency::USD], $this->replay(Generators::fromType('list<Currency>'), [1, 1, 0]));
    }

    public function testTheTypePassedToFromTypeMustBeParsable(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The type list<int cannot be parsed: unexpected end of the type, expected ">"');

        Generators::fromType('list<int');
    }

    public function testReportsATypePassedToFromTypeForWhichNoGeneratorCanBeDerivedWhenItsFirstValueIsGenerated(): void
    {
        $generator = Generators::fromType('mixed');

        $this->expectException(CannotDeriveGeneratorException::class);
        $this->expectExceptionMessage('mixed is not supported');

        $this->replay($generator, []);
    }

    public function testGeneratorsDerivedFromTypesUseTheGeneratorsRegisteredWithTheDeriverOfTheChoiceSource(): void
    {
        $money   = new Money(42, Currency::GBP);
        $deriver = new GeneratorDeriver(GeneratorRegistry::from([Money::class => Generators::constant($money)]));

        $this->assertSame($money, $this->replay(Generators::objects(Money::class), [], $deriver));
        $this->assertSame([$money], $this->replay(Generators::fromType('list<Money>'), [1, 0], $deriver));
        $this->assertSame($money, $this->replay(Generators::integers(0, 1)->flatMap(static fn (int $value): ValueGenerator => Generators::fromType('Money')), [], $deriver));
    }

    public function testRejectsTheExampleWhenItRequiresTooManyChoices(): void
    {
        $this->expectException(ExampleRejectedException::class);
        $this->expectExceptionMessage('Generating the example required more than 8192 choices');

        $this->replay(Generators::lists(Generators::integers(), 10000), []);
    }

    /**
     * @param ValueGenerator<mixed>  $generator
     * @param list<non-negative-int> $choices
     */
    private function replay(ValueGenerator $generator, array $choices, ?GeneratorDeriver $deriver = null): mixed
    {
        return AbstractGenerator::from($generator)->generate(ChoiceSource::replay(new ChoiceSequence($choices), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver));
    }

    /**
     * Generates values the way they are generated for the examples of a
     * property: a rejected example is skipped.
     *
     * @template T
     *
     * @param ValueGenerator<T> $generator
     * @param positive-int      $count
     *
     * @return list<T>
     */
    private function values(ValueGenerator $generator, int $count, int $seed = 1, ?GeneratorDeriver $deriver = null): array
    {
        $randomizer = new Randomizer(new Xoshiro256StarStar($seed));
        $values     = [];

        for ($attempt = 0; $attempt < 10 * $count && count($values) < $count; $attempt++) {
            try {
                $values[] = AbstractGenerator::from($generator)->generate(ChoiceSource::random($randomizer, ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver));
            } catch (ExampleRejectedException) {
                continue;
            }
        }

        $this->assertCount($count, $values);

        return $values;
    }

    /**
     * The depth of nested lists: 0 for a value that is not a list, 1 for a
     * list without nested lists, and so on.
     */
    private function depth(mixed $value): int
    {
        if (!is_array($value)) {
            return 0;
        }

        $depth = 0;

        foreach ($value as $element) {
            $depth = max($depth, $this->depth($element));
        }

        return $depth + 1;
    }

    /**
     * @param list<array<mixed>> $values
     */
    private function numberOfNonEmptyArrays(array $values): int
    {
        return count(array_filter($values, static fn (array $value): bool => $value !== []));
    }

    /**
     * @template T
     *
     * @param list<T> $values
     *
     * @return list<T>
     */
    private function distinct(array $values): array
    {
        $distinct = [];

        foreach ($values as $value) {
            if (!in_array($value, $distinct, true)) {
                $distinct[] = $value;
            }
        }

        return $distinct;
    }

    /**
     * @return ValueGenerator<mixed>
     */
    private function foreignGenerator(): ValueGenerator
    {
        return new class implements ValueGenerator
        {
            public function map(Closure $mapper): ValueGenerator
            {
                return $this;
            }

            public function filter(Closure $predicate): ValueGenerator
            {
                return $this;
            }

            public function flatMap(Closure $mapper): ValueGenerator
            {
                return $this;
            }
        };
    }
}
