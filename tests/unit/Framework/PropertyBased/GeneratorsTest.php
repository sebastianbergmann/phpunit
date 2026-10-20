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

use const FILTER_FLAG_GLOBAL_RANGE;
use const FILTER_FLAG_HOSTNAME;
use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_VALIDATE_DOMAIN;
use const FILTER_VALIDATE_EMAIL;
use const FILTER_VALIDATE_IP;
use const FILTER_VALIDATE_URL;
use const INF;
use const NAN;
use const PHP_FLOAT_MAX;
use const PHP_FLOAT_MIN;
use const PHP_INT_MAX;
use const PHP_INT_MIN;
use const PHP_URL_HOST;
use const PHP_URL_PATH;
use const PHP_URL_PORT;
use const PHP_URL_SCHEME;
use const PHP_URL_USER;
use const PREG_OFFSET_CAPTURE;
use function abs;
use function array_filter;
use function array_keys;
use function array_map;
use function array_merge;
use function array_unique;
use function array_values;
use function bin2hex;
use function count;
use function end;
use function explode;
use function filter_var;
use function implode;
use function in_array;
use function inet_ntop;
use function inet_pton;
use function ip2long;
use function is_array;
use function is_infinite;
use function is_nan;
use function long2ip;
use function max;
use function mb_check_encoding;
use function mb_ord;
use function mb_str_split;
use function mb_strlen;
use function parse_url;
use function preg_grep;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function serialize;
use function sort;
use function str_contains;
use function str_replace;
use function str_split;
use function str_starts_with;
use function strlen;
use function strrpos;
use function substr;
use function substr_count;
use Closure;
use DateTimeImmutable;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorRegistry;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ArrayGenerator;
use PHPUnit\Framework\PropertyBased\Generator\BooleanGenerator;
use PHPUnit\Framework\PropertyBased\Generator\BytesGenerator;
use PHPUnit\Framework\PropertyBased\Generator\CodePoints;
use PHPUnit\Framework\PropertyBased\Generator\ConstantGenerator;
use PHPUnit\Framework\PropertyBased\Generator\DateTimeGenerator;
use PHPUnit\Framework\PropertyBased\Generator\DomainNameGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ElementsGenerator;
use PHPUnit\Framework\PropertyBased\Generator\EmailAddressGenerator;
use PHPUnit\Framework\PropertyBased\Generator\FilteredGenerator;
use PHPUnit\Framework\PropertyBased\Generator\FlatMappedGenerator;
use PHPUnit\Framework\PropertyBased\Generator\FloatGenerator;
use PHPUnit\Framework\PropertyBased\Generator\IntegerGenerator;
use PHPUnit\Framework\PropertyBased\Generator\Ipv4AddressGenerator;
use PHPUnit\Framework\PropertyBased\Generator\Ipv6AddressGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ListGenerator;
use PHPUnit\Framework\PropertyBased\Generator\MappedGenerator;
use PHPUnit\Framework\PropertyBased\Generator\NullableGenerator;
use PHPUnit\Framework\PropertyBased\Generator\OneOfGenerator;
use PHPUnit\Framework\PropertyBased\Generator\RecordGenerator;
use PHPUnit\Framework\PropertyBased\Generator\Repetition;
use PHPUnit\Framework\PropertyBased\Generator\StringGenerator;
use PHPUnit\Framework\PropertyBased\Generator\TupleGenerator;
use PHPUnit\Framework\PropertyBased\Generator\UrlGenerator;
use PHPUnit\Framework\PropertyBased\Generator\UuidGenerator;
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
#[CoversClass(DateTimeGenerator::class)]
#[CoversClass(DomainNameGenerator::class)]
#[CoversClass(ElementsGenerator::class)]
#[CoversClass(EmailAddressGenerator::class)]
#[CoversClass(FilteredGenerator::class)]
#[CoversClass(FlatMappedGenerator::class)]
#[CoversClass(FloatGenerator::class)]
#[CoversClass(IntegerGenerator::class)]
#[CoversClass(Ipv4AddressGenerator::class)]
#[CoversClass(Ipv6AddressGenerator::class)]
#[CoversClass(ListGenerator::class)]
#[CoversClass(MappedGenerator::class)]
#[CoversClass(NullableGenerator::class)]
#[CoversClass(OneOfGenerator::class)]
#[CoversClass(RecordGenerator::class)]
#[CoversClass(Repetition::class)]
#[CoversClass(StringGenerator::class)]
#[CoversClass(TupleGenerator::class)]
#[CoversClass(UrlGenerator::class)]
#[CoversClass(UuidGenerator::class)]
#[CoversClass(ExampleRejectedException::class)]
#[CoversClass(InvalidGeneratorArgumentException::class)]
#[CoversClass(ValueGeneratorNotSupportedException::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class GeneratorsTest extends TestCase
{
    /**
     * An unquoted local part of an email address (RFC 5322).
     */
    private const string DOT_ATOM = '/^[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\/=?^_`{|}~-]+)*$/';

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
            'floats (options)'   => [Generators::floats(-1.5, 2.5, excludeMinimum: true, excludeMaximum: true, allowSubnormals: false)],
            'booleans'           => [Generators::booleans()],
            'strings'            => [Generators::strings()],
            'strings (alphabet)' => [Generators::strings('01', 1, 8)],
            'strings (ranges)'   => [Generators::strings(codePointRanges: [[0x41, 0x5A], [0x1F600, 0x1F64F]])],
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
            'uuids'              => [Generators::uuids(nilAndMax: true)],
            'IPv4 addresses'     => [Generators::ipv4Addresses()],
            'IPv6 addresses'     => [Generators::ipv6Addresses()],
            'domain names'       => [Generators::domainNames()],
            'email addresses'    => [Generators::emailAddresses(true, true)],
            'urls'               => [Generators::urls(userInformation: true)],
            'date times'         => [Generators::dateTimes(timezones: ['UTC', 'Europe/Berlin'], microseconds: true)],
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

    /**
     * @return array<string, array{float, float, bool, bool, bool, float, float}>
     */
    public static function rangeOfFloatsWithExcludedBoundsProvider(): array
    {
        return [
            'excluded minimum'                             => [0.0, 1.0, true, false, true, 4.9E-324, 1.0],
            'excluded maximum'                             => [0.0, 1.0, false, true, true, 0.0, 0.9999999999999999],
            'excluded minimum and maximum'                 => [-1.0, 1.0, true, true, true, -0.9999999999999999, 0.9999999999999999],
            'excluded positive minimum without subnormals' => [0.0, 1.0, true, false, false, PHP_FLOAT_MIN, 1.0],
            'excluded negative minimum without subnormals' => [-PHP_FLOAT_MIN, 1.0, true, false, false, -0.0, 1.0],
            'excluded negative maximum without subnormals' => [-1.0, 0.0, false, true, false, -1.0, -PHP_FLOAT_MIN],
            'excluded positive maximum without subnormals' => [-1.0, PHP_FLOAT_MIN, false, true, false, -1.0, 0.0],
        ];
    }

    /**
     * @return array<non-empty-string, array{string}>
     */
    public static function invalidIpv4NetworkProvider(): array
    {
        return [
            'without prefix'             => ['192.168.0.0'],
            'prefix longer than 32 bits' => ['192.168.0.0/33'],
            'prefix with leading zero'   => ['10.0.0.0/08'],
            'octet greater than 255'     => ['256.0.0.0/8'],
            'octet with leading zero'    => ['010.0.0.0/8'],
            'too few octets'             => ['10.0.0/8'],
            'IPv6 address'               => ['::/0'],
            'empty'                      => [''],
        ];
    }

    /**
     * @return array<non-empty-string, array{string}>
     */
    public static function invalidIpv6NetworkProvider(): array
    {
        return [
            'without prefix'               => ['2001:db8::'],
            'prefix longer than 128 bits'  => ['2001:db8::/129'],
            'prefix with leading zero'     => ['2001:db8::/032'],
            'zone identifier'              => ['fe80::%eth0/64'],
            'two runs of zeros compressed' => ['2001::db8::/32'],
            'IPv4 address'                 => ['10.0.0.0/8'],
            'empty'                        => [''],
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

    #[DataProvider('rangeOfFloatsWithExcludedBoundsProvider')]
    public function testGeneratesFloatsWithinTheRangeWithoutItsExcludedBounds(float $minimum, float $maximum, bool $excludeMinimum, bool $excludeMaximum, bool $allowSubnormals, float $smallest, float $largest): void
    {
        $values = $this->values(Generators::floats($minimum, $maximum, excludeMinimum: $excludeMinimum, excludeMaximum: $excludeMaximum, allowSubnormals: $allowSubnormals), 2000);

        foreach ($values as $value) {
            $this->assertGreaterThanOrEqual($smallest, $value);
            $this->assertLessThanOrEqual($largest, $value);
        }

        // the bounds of the range are among the special values, which are
        // generated often
        $this->assertContains(serialize($smallest), array_map(serialize(...), $values));
        $this->assertContains(serialize($largest), array_map(serialize(...), $values));
    }

    public function testGeneratesNoSubnormalFloatsWhenTheyAreNotAllowed(): void
    {
        $values = $this->values(Generators::floats(allowSubnormals: false), 2000);

        $this->assertEmpty(array_filter($values, static fn (float $value): bool => $value !== 0.0 && abs($value) < PHP_FLOAT_MIN));
        $this->assertContains(serialize(-0.0), array_map(serialize(...), $values));
        $this->assertContains(serialize(PHP_FLOAT_MIN), array_map(serialize(...), $values));
    }

    public function testReplacesASubnormalFloatWithTheZeroOfItsSignWhenSubnormalFloatsAreNotAllowed(): void
    {
        $generator = Generators::floats(-1.0, 1.0, allowSubnormals: false);

        $this->assertSame(serialize(0.0), serialize($this->replay($generator, FloatGenerator::encode(4.9E-324))));
        $this->assertSame(serialize(-0.0), serialize($this->replay($generator, FloatGenerator::encode(-4.9E-324))));
    }

    public function testExcludingTheBoundsOfARangeMustLeaveAFloatInIt(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The range contains no float when its excluded bounds are left out');

        Generators::floats(1.0, 1.0, excludeMinimum: true);
    }

    public function testTheBoundsOfARangeMustNotBeSubnormalWhenSubnormalFloatsAreNotAllowed(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The minimum and the maximum must not be subnormal when subnormal floats are not allowed');

        Generators::floats(4.9E-324, 1.0, allowSubnormals: false);
    }

    public function testGeneratesStringsOfCharactersFromCodePointRanges(): void
    {
        $characters = [];

        foreach ($this->values(Generators::strings(minimumLength: 1, maximumLength: 10, codePointRanges: [[0x30, 0x39], [0x4E00, 0x9FFF]]), 500) as $value) {
            $this->assertTrue(mb_check_encoding($value, 'UTF-8'));

            foreach (mb_str_split($value) as $character) {
                $codePoint = mb_ord($character);

                $this->assertTrue(($codePoint >= 0x30 && $codePoint <= 0x39) || ($codePoint >= 0x4E00 && $codePoint <= 0x9FFF));

                $characters[] = $codePoint;
            }
        }

        // the characters of the small range are chosen about as often as
        // those of the large one
        $digits = count(array_filter($characters, static fn (int $codePoint): bool => $codePoint <= 0x39));

        $this->assertGreaterThan(count($characters) / 4, $digits);
        $this->assertLessThan(3 * count($characters) / 4, $digits);
    }

    public function testTheSimplestStringOfCharactersFromCodePointRangesConsistsOfTheLowestCodePoint(): void
    {
        $this->assertSame('00', $this->replay(Generators::strings(minimumLength: 2, codePointRanges: [[0x41, 0x5A], [0x30, 0x39]]), []));
    }

    public function testEitherAnAlphabetOrCodePointRangesCanBeSpecified(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('Either an alphabet or code point ranges can be specified, not both');

        Generators::strings('ab', codePointRanges: [[0x41, 0x5A]]);
    }

    public function testGeneratesUuidsOfEveryVersionInTheirCanonicalFormWithTheirVersionAndTheVariant(): void
    {
        $versions = [];

        foreach ($this->values(Generators::uuids(), 1000) as $value) {
            $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value);

            $versions[$value[14]] = true;
        }

        $versions = array_keys($versions);

        sort($versions);

        $this->assertSame([1, 2, 3, 4, 5, 6, 7, 8], $versions);
    }

    public function testGeneratesUuidsOfTheVersionsThatAreSpecified(): void
    {
        foreach ($this->values(Generators::uuids([4, 7]), 500) as $value) {
            $this->assertContains($value[14], ['4', '7']);
        }
    }

    public function testGeneratesUuidsWithFieldsOfOnlyZerosAndOfOnlyOnes(): void
    {
        $nodes = array_map(static fn (string $value): string => substr($value, 24), $this->values(Generators::uuids([4]), 1000));

        $this->assertContains('000000000000', $nodes);
        $this->assertContains('ffffffffffff', $nodes);
    }

    public function testTheTimestampOfAUuidIsLaidOutAsItsVersionRequires(): void
    {
        $this->assertSame('89abcdef-4567-1123-8000-000000000000', $this->replay(Generators::uuids([1]), [0, 0x123456789ABCDEF]));
        $this->assertSame('12345678-9abc-6def-8000-000000000000', $this->replay(Generators::uuids([6]), [0, 0x123456789ABCDEF]));
        $this->assertSame('00000000-0001-7000-8000-000000000000', $this->replay(Generators::uuids([7]), [0, 1]));
    }

    public function testTheSimplestUuidIsOfTheFirstVersionWithOnlyZerosInItsOtherBits(): void
    {
        $this->assertSame('00000000-0000-1000-8000-000000000000', $this->replay(Generators::uuids(), []));
        $this->assertSame('00000000-0000-4000-8000-000000000000', $this->replay(Generators::uuids([4]), []));
    }

    public function testGeneratesTheNilAndTheMaxUuidOnlyWhenTheyAreRequested(): void
    {
        $values = $this->values(Generators::uuids(nilAndMax: true), 1000);

        $this->assertContains('00000000-0000-0000-0000-000000000000', $values);
        $this->assertContains('ffffffff-ffff-ffff-ffff-ffffffffffff', $values);
        $this->assertSame('00000000-0000-0000-0000-000000000000', $this->replay(Generators::uuids(nilAndMax: true), []));

        $this->assertNotContains('00000000-0000-0000-0000-000000000000', $this->values(Generators::uuids(), 1000));
    }

    public function testAtLeastOneVersionOfUuidsMustBeSpecified(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('At least one version of UUIDs must be specified');

        Generators::uuids([]);
    }

    public function testTheVersionsOfUuidsMustBeFromOneToEight(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The version of UUIDs (9) must be from 1 to 8');

        Generators::uuids([4, 9]);
    }

    public function testGeneratesValidIpv4AddressesInDottedDecimalNotation(): void
    {
        foreach ($this->values(Generators::ipv4Addresses(), 1000) as $value) {
            $this->assertNotFalse(filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4));

            $address = ip2long($value);

            $this->assertNotFalse($address);
            $this->assertSame($value, long2ip($address));
        }
    }

    public function testGeneratesIpv4AddressesFromTheSpecialPurposeRangesAndRightNextToThem(): void
    {
        $values = $this->values(Generators::ipv4Addresses(), 2000);

        $special = array_filter(
            $values,
            static fn (string $value): bool => filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_GLOBAL_RANGE) === false,
        );

        $this->assertGreaterThan(800, count($special));
        $this->assertContains('9.255.255.255', $values);
        $this->assertContains('11.0.0.0', $values);
        $this->assertContains('0.0.0.0', $values);
        $this->assertContains('255.255.255.255', $values);
    }

    public function testGeneratesTheFirstAndTheLastAddressOfTheSpecialPurposeRanges(): void
    {
        $this->assertSame('10.0.0.0', $this->replay(Generators::ipv4Addresses('10.0.0.0/8'), [1, 0, 0]));
        $this->assertSame('10.255.255.255', $this->replay(Generators::ipv4Addresses('10.0.0.0/8'), [1, 0, 0xFFFFFF]));
    }

    public function testGeneratesIpv4AddressesOfTheNetworkThatIsSpecified(): void
    {
        $values = $this->values(Generators::ipv4Addresses('192.168.0.0/16'), 1000);

        foreach ($values as $value) {
            $this->assertStringStartsWith('192.168.', $value);
        }

        $this->assertContains('192.168.0.0', $values);
        $this->assertContains('192.168.255.255', $values);
    }

    public function testGeneratesIpv4AddressesOfANetworkThatOverlapsNoSpecialPurposeRange(): void
    {
        foreach ($this->values(Generators::ipv4Addresses('8.8.8.0/24'), 200) as $value) {
            $this->assertStringStartsWith('8.8.8.', $value);
        }
    }

    public function testGeneratesTheOnlyIpv4AddressOfANetworkWithAPrefixOf32Bits(): void
    {
        $this->assertSame(['1.2.3.4'], array_values(array_unique($this->values(Generators::ipv4Addresses('1.2.3.4/32'), 100))));
    }

    public function testTheSimplestIpv4AddressIsTheNetworkAddress(): void
    {
        $this->assertSame('0.0.0.0', $this->replay(Generators::ipv4Addresses(), []));
        $this->assertSame('192.168.0.0', $this->replay(Generators::ipv4Addresses('192.168.0.0/16'), []));
    }

    #[DataProvider('invalidIpv4NetworkProvider')]
    public function testTheNetworkOfIpv4AddressesMustBeGivenInCidrNotation(string $network): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The network (' . $network . ') must be an IPv4 address and the length of its prefix, from 0 to 32, in CIDR notation, for instance 192.168.0.0/16');

        Generators::ipv4Addresses($network);
    }

    public function testTheNetworkOfIpv4AddressesMustBeGivenAsItsNetworkAddress(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The address of the network (192.168.1.1/16) must be its network address, whose host bits are zero: 192.168.0.0/16');

        Generators::ipv4Addresses('192.168.1.1/16');
    }

    public function testGeneratesValidIpv6AddressesInTheirCanonicalTextRepresentation(): void
    {
        foreach ($this->values(Generators::ipv6Addresses(), 2000) as $value) {
            $this->assertNotFalse(filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6));

            $bytes = inet_pton($value);

            $this->assertNotFalse($bytes);
            $this->assertSame($this->canonicalIpv6Address($bytes), $value);
        }
    }

    public function testGeneratesIpv6AddressesFromTheSpecialPurposeRanges(): void
    {
        $values = $this->values(Generators::ipv6Addresses(), 2000);

        $this->assertContains('::', $values);
        $this->assertContains('::1', $values);

        foreach (['/^::ffff:\d+\.\d+\.\d+\.\d+$/', '/^64:ff9b::/', '/^2001:db8:/', '/^f[cd][0-9a-f]{2}:/', '/^fe[89ab][0-9a-f]:/', '/^ff[0-9a-f]{2}:/'] as $pattern) {
            $this->assertNotEmpty(preg_grep($pattern, $values), $pattern);
        }
    }

    public function testGeneratesIpv6AddressesWithRunsOfZerosOfEveryLength(): void
    {
        $lengths = [];

        foreach ($this->values(Generators::ipv6Addresses(), 2000) as $value) {
            if (str_contains($value, '::') && !str_contains($value, '.')) {
                $lengths[8 - count(array_filter(explode(':', $value), static fn (string $group): bool => $group !== ''))] = true;
            }
        }

        $lengths = array_keys($lengths);

        sort($lengths);

        $this->assertSame([2, 3, 4, 5, 6, 7, 8], $lengths);
    }

    public function testGeneratesIpv6AddressesOfTheNetworkThatIsSpecified(): void
    {
        $values = $this->values(Generators::ipv6Addresses('2001:db8::/120'), 1000);

        foreach ($values as $value) {
            $this->assertMatchesRegularExpression('/^2001:db8::([0-9a-f]{1,2})?$/', $value);
        }

        $this->assertContains('2001:db8::', $values);
        $this->assertContains('2001:db8::ff', $values);
    }

    public function testGeneratesIpv6AddressesOfANetworkInASpecialPurposeRange(): void
    {
        foreach ($this->values(Generators::ipv6Addresses('fe80::/64'), 200) as $value) {
            $this->assertStringStartsWith('fe80::', $value);
        }
    }

    public function testGeneratesTheSpecialPurposeAddressesOfANetworkThatContainsThemOnly(): void
    {
        $values = array_values(array_unique($this->values(Generators::ipv6Addresses('::/127'), 100)));

        sort($values);

        $this->assertSame(['::', '::1'], $values);
    }

    public function testTheLast32BitsOfAnIpv4MappedAddressAreInDottedDecimalNotation(): void
    {
        $this->assertSame('::ffff:192.0.2.1', $this->replay(Generators::ipv6Addresses('::ffff:0:0/96'), [0, 0xC000, 0x201]));
    }

    public function testTheSimplestIpv6AddressIsTheNetworkAddress(): void
    {
        $this->assertSame('::', $this->replay(Generators::ipv6Addresses(), []));
        $this->assertSame('2001:db8::', $this->replay(Generators::ipv6Addresses('2001:db8::/32'), []));
    }

    #[DataProvider('invalidIpv6NetworkProvider')]
    public function testTheNetworkOfIpv6AddressesMustBeGivenInCidrNotation(string $network): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The network (' . $network . ') must be an IPv6 address and the length of its prefix, from 0 to 128, in CIDR notation, for instance 2001:db8::/32');

        Generators::ipv6Addresses($network);
    }

    public function testTheNetworkOfIpv6AddressesMustBeGivenAsItsNetworkAddress(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The address of the network (2001:DB8::1/32) must be its network address, whose host bits are zero: 2001:db8::/32');

        Generators::ipv6Addresses('2001:DB8::1/32');
    }

    public function testGeneratesValidDomainNames(): void
    {
        foreach ($this->values(Generators::domainNames(), 1000) as $value) {
            $this->assertNotFalse(filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME));
            $this->assertLessThanOrEqual(253, strlen($value));

            $labels = explode('.', $value);

            $this->assertGreaterThanOrEqual(2, count($labels));
            $this->assertMatchesRegularExpression('/^[a-z]{2,63}$/', (string) end($labels));

            foreach ($labels as $label) {
                $this->assertMatchesRegularExpression('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/', $label);
                $this->assertDoesNotMatchRegularExpression('/^..--/', $label);
            }
        }
    }

    public function testGeneratesDomainNamesWithLongLabelsNumericLabelsAndManyLabelsAndOfTheMaximumLength(): void
    {
        $values = $this->values(Generators::domainNames(), 1000);
        $labels = array_merge(...array_map(static fn (string $value): array => explode('.', $value), $values));

        $this->assertNotEmpty(array_filter($labels, static fn (string $label): bool => strlen($label) === 63));
        $this->assertNotEmpty(array_filter($labels, static fn (string $label): bool => preg_match('/^[0-9]{2,}$/', $label) === 1));
        $this->assertNotEmpty(array_filter($values, static fn (string $value): bool => substr_count($value, '.') >= 9));
        $this->assertNotEmpty(array_filter($values, static fn (string $value): bool => strlen($value) === 253));
    }

    public function testGeneratesDomainNamesOfAtMostTheMaximumLength(): void
    {
        $lengths = array_map(strlen(...), $this->values(Generators::domainNames(20), 500));

        $this->assertLessThanOrEqual(20, max($lengths));
        $this->assertContains(20, $lengths);
        $this->assertContains(4, $lengths);
    }

    public function testGeneratesDomainNamesOfTheShortestMaximumLength(): void
    {
        foreach ($this->values(Generators::domainNames(4), 100) as $value) {
            $this->assertMatchesRegularExpression('/^[a-z0-9]\.[a-z]{2}$/', $value);
        }
    }

    public function testTheSimplestDomainNameHasALabelOfOneLetterAndATopLevelDomainOfTwoLetters(): void
    {
        $this->assertSame('a.aa', $this->replay(Generators::domainNames(), []));
    }

    #[TestWith([3])]
    #[TestWith([254])]
    public function testTheMaximumLengthOfDomainNamesMustBeFromFourTo253(int $maximumLength): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The maximum length of domain names (' . $maximumLength . ') must be from 4 to 253');

        Generators::domainNames($maximumLength);
    }

    public function testGeneratesEmailAddressesThatPassFilterVar(): void
    {
        foreach ([false, true] as $ipAddressLiterals) {
            foreach ($this->values(Generators::emailAddresses(ipAddressLiterals: $ipAddressLiterals), 1000) as $value) {
                $this->assertNotFalse(filter_var($value, FILTER_VALIDATE_EMAIL), $value);
                $this->assertLessThanOrEqual(254, strlen($value));
                $this->assertLessThanOrEqual(64, strlen($this->localPartOf($value)));
                $this->assertMatchesRegularExpression(self::DOT_ATOM, $this->localPartOf($value));
            }
        }
    }

    public function testGeneratesEmailAddressesWithLongLocalPartsAndSpecialCharactersAndOfTheMaximumLength(): void
    {
        $values     = $this->values(Generators::emailAddresses(), 1000);
        $localParts = array_map($this->localPartOf(...), $values);

        $this->assertContains(64, array_map(strlen(...), $localParts));
        $this->assertContains(254, array_map(strlen(...), $values));

        foreach (['/\+/', '/[A-Z]/', '/\./', '/[!#$%&\'*\/=?^_`{|}~-]/'] as $pattern) {
            $this->assertNotEmpty(preg_grep($pattern, $localParts), $pattern);
        }
    }

    public function testGeneratesEmailAddressesWithQuotedLocalPartsOnlyWhenRequested(): void
    {
        $localParts = array_map($this->localPartOf(...), $this->values(Generators::emailAddresses(quotedLocalParts: true), 1000));
        $quoted     = preg_grep('/^"/', $localParts);

        foreach ($localParts as $localPart) {
            $this->assertLessThanOrEqual(64, strlen($localPart));
        }

        foreach ($quoted as $localPart) {
            $this->assertMatchesRegularExpression('/^"(?:[\x20\x21\x23-\x5B\x5D-\x7E]|\\\\["\\\\])+"$/', $localPart);
        }

        $this->assertGreaterThan(100, count($quoted));
        $this->assertNotEmpty(preg_grep('/ /', $quoted));
        $this->assertNotEmpty(preg_grep('/\\\\["\\\\]/', $quoted));
        $this->assertCount(1000 - count($quoted), preg_grep(self::DOT_ATOM, $localParts));
    }

    public function testAQuoteOrABackslashInAQuotedLocalPartIsEscapedWhenItFits(): void
    {
        // the choices of the domain a.aa: two letters for the top-level
        // domain, a label of one letter, and no further label
        $domain = [0, 0, 0, 0, 0, 0, 0];

        $this->assertSame('"\\""@a.aa', $this->replay(Generators::emailAddresses(quotedLocalParts: true), [...$domain, 1, 1, 93]));
        $this->assertSame('"\\\\"@a.aa', $this->replay(Generators::emailAddresses(quotedLocalParts: true), [...$domain, 1, 1, 94]));
        $this->assertSame('"~"@a.aa', $this->replay(Generators::emailAddresses(quotedLocalParts: true), [...$domain, 1, 0, 93]));
    }

    public function testGeneratesEmailAddressesWithAddressLiteralsOnlyWhenRequested(): void
    {
        $domains = array_map(
            static fn (string $value): string => substr($value, (int) strrpos($value, '@') + 1),
            $this->values(Generators::emailAddresses(ipAddressLiterals: true), 1000),
        );

        $ipv4Addresses = [];
        $ipv6Addresses = [];

        foreach ($domains as $domain) {
            if (preg_match('/^\[IPv6:(.+)\]$/', $domain, $matches) === 1) {
                $ipv6Addresses[] = $matches[1];
            } elseif (preg_match('/^\[(.+)\]$/', $domain, $matches) === 1) {
                $ipv4Addresses[] = $matches[1];
            }
        }

        $this->assertNotEmpty($ipv4Addresses);
        $this->assertNotEmpty($ipv6Addresses);

        foreach ($ipv4Addresses as $address) {
            $this->assertNotFalse(filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4));
        }

        foreach ($ipv6Addresses as $address) {
            $this->assertNotFalse(filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6));
        }

        $this->assertEmpty(preg_grep('/\[/', $this->values(Generators::emailAddresses(), 1000)));
    }

    public function testTheSimplestEmailAddressIsAAtAAa(): void
    {
        $this->assertSame('a@a.aa', $this->replay(Generators::emailAddresses(), []));
        $this->assertSame('a@a.aa', $this->replay(Generators::emailAddresses(true, true), []));
    }

    public function testGeneratesNormalizedUrlsThatPassFilterVar(): void
    {
        foreach ($this->values(Generators::urls(), 1000) as $value) {
            $this->assertNotFalse(filter_var($value, FILTER_VALIDATE_URL), $value);

            $this->assertMatchesRegularExpression('/^https?:\/\/[^\/?#]+(?:\/(?:[A-Za-z0-9\-._~!$&\'()*+,;=:@]|%[0-9A-F]{2})*)*(?:\?(?:[A-Za-z0-9\-._~!$&\'()*+,;=:@\/?]|%[0-9A-F]{2})*)?(?:#(?:[A-Za-z0-9\-._~!$&\'()*+,;=:@\/?]|%[0-9A-F]{2})*)?$/', $value);
            $this->assertDoesNotMatchRegularExpression('/^[^?#]*\/\.\.?(?:[\/?#]|$)/', $value);
            $this->assertDoesNotMatchRegularExpression('/%(?:3[0-9]|4[1-9A-F]|5[0-9A]|6[1-9A-F]|7[0-9A]|2D|2E|5F|7E)/', $value);
        }
    }

    public function testGeneratesUrlsWithEveryKindOfHostPortsPathsQueriesAndFragments(): void
    {
        $values = $this->values(Generators::urls(), 1000);
        $hosts  = array_map(static fn (string $value): string => (string) parse_url($value, PHP_URL_HOST), $values);
        $ports  = array_map(static fn (string $value): ?int => parse_url($value, PHP_URL_PORT), $values);
        $paths  = array_map(static fn (string $value): string => (string) parse_url($value, PHP_URL_PATH), $values);

        $this->assertNotEmpty(array_filter($hosts, static fn (string $host): bool => filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false));
        $this->assertNotEmpty(preg_grep('/^\[.+\]$/', $hosts));
        $this->assertNotEmpty(preg_grep('/^[a-z0-9.-]+$/', $hosts));

        $this->assertContains(1, $ports);
        $this->assertContains(65535, $ports);
        $this->assertContains(80, $ports);
        $this->assertContains(null, $ports);

        $this->assertContains('', $paths);
        $this->assertNotEmpty(preg_grep('/.\/$/', $paths));

        foreach (['/\?/', '/#/', '/%20/', '/%2F/', '/%C3%A4/', '/%E2%82%AC/', '/%F0%9F%98%80/'] as $pattern) {
            $this->assertNotEmpty(preg_grep($pattern, $values), $pattern);
        }
    }

    public function testGeneratesUrlsOfTheSchemesThatAreSpecified(): void
    {
        $schemes = array_values(array_unique(array_map(static fn (string $value): string => (string) parse_url($value, PHP_URL_SCHEME), $this->values(Generators::urls(['ftp', 'git+ssh']), 200))));

        sort($schemes);

        $this->assertSame(['ftp', 'git+ssh'], $schemes);
    }

    public function testGeneratesUrlsWithUserInformationOnlyWhenRequested(): void
    {
        $values = $this->values(Generators::urls(userInformation: true), 1000);
        $users  = [];

        foreach ($values as $value) {
            if (preg_match('/^https?:\/\/([^\/?#@]+)@/', $value, $matches) !== 1) {
                continue;
            }

            $this->assertMatchesRegularExpression('/^(?:[A-Za-z0-9\-._~!$&\'()*+,;=]|%[0-9A-F]{2})+(?::(?:[A-Za-z0-9\-._~!$&\'()*+,;=]|%[0-9A-F]{2})+)?$/', $matches[1]);
            $this->assertNotFalse(filter_var(str_replace($matches[1] . '@', '', $value), FILTER_VALIDATE_URL));

            $users[] = $matches[1];
        }

        $this->assertGreaterThan(200, count($users));
        $this->assertNotEmpty(preg_grep('/:/', $users));
        $this->assertNotEmpty(preg_grep('/^[^:]+$/', $users));

        foreach ($this->values(Generators::urls(), 1000) as $value) {
            $this->assertNull(parse_url($value, PHP_URL_USER));
        }
    }

    public function testTheSimplestUrlIsOfTheFirstSchemeAndHasTheSimplestDomainNameAsItsHost(): void
    {
        $this->assertSame('http://a.aa', $this->replay(Generators::urls(), []));
        $this->assertSame('https://a.aa', $this->replay(Generators::urls(['https', 'http'], true), []));
    }

    public function testAtLeastOneSchemeOfUrlsMustBeSpecified(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('At least one scheme of URLs must be specified');

        Generators::urls([]);
    }

    #[TestWith(['HTTP'])]
    #[TestWith(['1http'])]
    #[TestWith(['http:'])]
    #[TestWith([''])]
    public function testTheSchemesOfUrlsMustBeValidAndLowercase(string $scheme): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The scheme of URLs (' . $scheme . ') must be a lowercase letter followed by lowercase letters, digits, "+", "-", and "."');

        Generators::urls(['http', $scheme]);
    }

    public function testGeneratesPointsInTimeWithinTheRangeInTheTimeZones(): void
    {
        $minimum = new DateTimeImmutable('2000-01-01 00:00:00 UTC');
        $maximum = new DateTimeImmutable('2030-12-31 23:59:59 UTC');

        foreach ($this->values(Generators::dateTimes($minimum, $maximum, ['Europe/Berlin', 'Asia/Kathmandu']), 1000) as $value) {
            $this->assertGreaterThanOrEqual($minimum, $value);
            $this->assertLessThanOrEqual($maximum, $value);
            $this->assertContains($value->getTimezone()->getName(), ['Europe/Berlin', 'Asia/Kathmandu']);
            $this->assertSame('000000', $value->format('u'));
        }
    }

    public function testGeneratesPointsInTimeInTimeZonesThatAreGivenAsOffsetsOrAbbreviations(): void
    {
        foreach ($this->values(Generators::dateTimes(timezones: ['+05:30', 'EST']), 500) as $value) {
            $this->assertContains($value->getOffset(), [19800, -18000]);
        }
    }

    public function testGeneratesNotablePointsInTimeAndTheBoundsOfTheRange(): void
    {
        $values     = $this->values(Generators::dateTimes(), 2000);
        $timestamps = array_map(static fn (DateTimeImmutable $value): int => $value->getTimestamp(), $values);
        $formatted  = array_map(static fn (DateTimeImmutable $value): string => $value->format('Y-m-d H:i:s'), $values);

        foreach ([0, 2147483647, 2147483648, -2147483648, -2147483649, 946684799, 946684800, 4294967295, 4294967296] as $timestamp) {
            $this->assertContains($timestamp, $timestamps);
        }

        $this->assertContains('0001-01-01 00:00:00', $formatted);
        $this->assertContains('9999-12-31 23:59:59', $formatted);

        foreach (['/-02-29 /', '/-(0[2-9]|1[0-2])-01 00:00:00$/', '/-(0[13578]|1[02])-31 23:59:59$/', '/-12-31 23:59:59$/'] as $pattern) {
            $this->assertNotEmpty(preg_grep($pattern, $formatted), $pattern);
        }
    }

    public function testGeneratesTheTransitionsBetweenStandardTimeAndDaylightSavingTimeAndTheSecondBeforeThem(): void
    {
        $values = $this->values(Generators::dateTimes(timezones: ['Europe/Berlin']), 1000);

        $this->assertNotEmpty(array_filter($values, static fn (DateTimeImmutable $value): bool => $value->getOffset() !== $value->modify('-1 second')->getOffset()));
        $this->assertNotEmpty(array_filter($values, static fn (DateTimeImmutable $value): bool => $value->getOffset() !== $value->modify('+1 second')->getOffset()));
    }

    public function testGeneratesLeapDaysOfTheLeapYearsInTheRangeOnly(): void
    {
        $values = $this->values(Generators::dateTimes(new DateTimeImmutable('2096-01-01 UTC'), new DateTimeImmutable('2104-12-31 UTC')), 500);
        $years  = array_values(array_unique(array_map(static fn (DateTimeImmutable $value): string => $value->format('Y'), array_filter($values, static fn (DateTimeImmutable $value): bool => $value->format('m-d') === '02-29'))));

        sort($years);

        $this->assertSame(['2096', '2104'], $years);

        foreach ($this->values(Generators::dateTimes(new DateTimeImmutable('2001-01-01 UTC'), new DateTimeImmutable('2003-12-31 UTC')), 200) as $value) {
            $this->assertNotSame('02-29', $value->format('m-d'));
        }
    }

    public function testGeneratesPointsInTimeWithMicrosecondsOnlyWhenRequested(): void
    {
        foreach ($this->values(Generators::dateTimes(), 500) as $value) {
            $this->assertSame('000000', $value->format('u'));
        }

        $microseconds = array_map(static fn (DateTimeImmutable $value): string => $value->format('u'), $this->values(Generators::dateTimes(microseconds: true), 500));

        $this->assertContains('999999', $microseconds);
        $this->assertGreaterThan(100, count(array_filter($microseconds, static fn (string $value): bool => $value !== '000000')));
    }

    public function testGeneratesPointsInTimeOnlyFromTheRangeWhoseBoundsHaveMicroseconds(): void
    {
        $minimum = new DateTimeImmutable('2020-01-01 00:00:00.5 UTC');
        $maximum = new DateTimeImmutable('2020-01-01 00:00:02.25 UTC');

        $this->assertSame(
            ['2020-01-01 00:00:01.000000', '2020-01-01 00:00:02.000000'],
            $this->sortedUnique(array_map(static fn (DateTimeImmutable $value): string => $value->format('Y-m-d H:i:s.u'), $this->values(Generators::dateTimes($minimum, $maximum), 200))),
        );

        foreach ($this->values(Generators::dateTimes($minimum, $maximum, microseconds: true), 200) as $value) {
            $this->assertGreaterThanOrEqual($minimum, $value);
            $this->assertLessThanOrEqual($maximum, $value);
        }

        $this->assertSame('2020-01-01 00:00:00.500000', $this->replay(Generators::dateTimes($minimum, $maximum, microseconds: true), [])->format('Y-m-d H:i:s.u'));
    }

    public function testTheSimplestPointInTimeIsTheEpochInTheFirstTimeZoneOrTheBoundClosestToIt(): void
    {
        $this->assertSame('1970-01-01T00:00:00+00:00', $this->replay(Generators::dateTimes(), [])->format(DateTimeInterface::ATOM));
        $this->assertSame('1970-01-01T09:00:00+09:00', $this->replay(Generators::dateTimes(timezones: ['Asia/Tokyo', 'UTC']), [])->format(DateTimeInterface::ATOM));
        $this->assertSame('2000-01-01T00:00:00+00:00', $this->replay(Generators::dateTimes(new DateTimeImmutable('2000-01-01 UTC')), [])->format(DateTimeInterface::ATOM));
    }

    public function testTheMinimumOfPointsInTimeMustNotBeGreaterThanTheirMaximum(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The minimum (2020-01-02T00:00:00.000+00:00) must not be greater than the maximum (2020-01-01T00:00:00.000+00:00)');

        Generators::dateTimes(new DateTimeImmutable('2020-01-02 UTC'), new DateTimeImmutable('2020-01-01 UTC'));
    }

    public function testAtLeastOneTimeZoneMustBeSpecified(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('At least one time zone must be specified');

        Generators::dateTimes(timezones: []);
    }

    public function testTheTimeZonesMustBeValid(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The time zone (Mars/Olympus_Mons) must be an identifier, an abbreviation, or an offset from UTC');

        Generators::dateTimes(timezones: ['UTC', 'Mars/Olympus_Mons']);
    }

    public function testTheRangeOfPointsInTimeWithoutMicrosecondsMustContainAWholeSecond(): void
    {
        $this->expectException(InvalidGeneratorArgumentException::class);
        $this->expectExceptionMessage('The range from the minimum (2020-01-01T00:00:00.100+00:00) to the maximum (2020-01-01T00:00:00.900+00:00) must contain a whole second, use $microseconds to generate points in time with microseconds');

        Generators::dateTimes(new DateTimeImmutable('2020-01-01 00:00:00.1 UTC'), new DateTimeImmutable('2020-01-01 00:00:00.9 UTC'));
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

    public function testTheDeepestLevelOfARecursiveValueChoosesTheValueOfTheBaseGeneratorLikeTheOtherLevels(): void
    {
        $generator = AbstractGenerator::from(
            Generators::recursive(
                Generators::integers(0, 9),
                static fn (ValueGenerator $values): ValueGenerator => Generators::lists($values),
                1,
            ),
        );

        $source = ChoiceSource::replay(new ChoiceSequence([1, 1, 0, 5, 0]));

        $this->assertSame([5], $generator->generate($source));

        $spansOfLevels = array_values(
            array_filter(
                $source->sequence()->spans(),
                static fn (Span $span): bool => $span->label() === $generator->label(),
            ),
        );

        $this->assertSame(
            [[0, 5], [2, 4]],
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
     * The canonical text representation (RFC 5952) of the address with the
     * bytes: the hexadecimal groups without leading zeros, of which the first
     * longest run of at least two groups of zeros is compressed, or the
     * dotted decimal notation of the last 32 bits for an IPv4-mapped address.
     */
    private function canonicalIpv6Address(string $bytes): string
    {
        if (str_starts_with($bytes, "\0\0\0\0\0\0\0\0\0\0\xFF\xFF")) {
            return '::ffff:' . inet_ntop(substr($bytes, 12));
        }

        $uncompressed = preg_replace('/(?<=^|:)0+(?=[0-9a-f])/', '', implode(':', str_split(bin2hex($bytes), 4)));

        $this->assertIsString($uncompressed);

        $runs = [];

        preg_match_all('/(?<![0-9a-f])0(?::0)+(?![0-9a-f])/', $uncompressed, $runs, PREG_OFFSET_CAPTURE);

        $longest = null;

        foreach ($runs[0] as $run) {
            if ($longest === null || strlen($run[0]) > strlen($longest[0])) {
                $longest = $run;
            }
        }

        if ($longest === null) {
            return $uncompressed;
        }

        $before = substr($uncompressed, 0, $longest[1]);
        $after  = substr($uncompressed, $longest[1] + strlen($longest[0]));

        if ($before === '') {
            $before = ':';
        }

        if ($after === '') {
            $after = ':';
        }

        return $before . $after;
    }

    /**
     * @return non-empty-string
     */
    private function localPartOf(string $emailAddress): string
    {
        $localPart = substr($emailAddress, 0, (int) strrpos($emailAddress, '@'));

        $this->assertNotSame('', $localPart);

        return $localPart;
    }

    /**
     * @param list<string> $values
     *
     * @return list<string>
     */
    private function sortedUnique(array $values): array
    {
        $values = array_values(array_unique($values));

        sort($values);

        return $values;
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
