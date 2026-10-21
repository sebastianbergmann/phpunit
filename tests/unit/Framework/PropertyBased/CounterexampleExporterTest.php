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
use const PHP_INT_MAX;
use const PHP_INT_MIN;
use function fclose;
use function fopen;
use function serialize;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorRegistry;
use PHPUnit\Framework\PropertyBased\Type\NameScope;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\Currency;
use PHPUnit\TestFixture\PropertyBased\Derivation\ClassWithoutConstructor;
use PHPUnit\TestFixture\PropertyBased\Derivation\Counter;
use PHPUnit\TestFixture\PropertyBased\Derivation\ExampleEmailAddress;
use PHPUnit\TestFixture\PropertyBased\Derivation\Money;
use PHPUnit\TestFixture\PropertyBased\Derivation\Node;
use PHPUnit\TestFixture\PropertyBased\Derivation\Parameters;
use PHPUnit\TestFixture\PropertyBased\NameResolution\ClassWithImports;
use PropertyBasedNameResolutionClassInGlobalNamespace;
use ReflectionMethod;

#[CoversClass(CounterexampleExporter::class)]
#[CoversClass(Constructions::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class CounterexampleExporterTest extends TestCase
{
    /**
     * @return array<string, array{string, mixed}>
     */
    public static function valueProvider(): array
    {
        return [
            'null'                                       => ['null', null],
            'true'                                       => ['true', true],
            'false'                                      => ['false', false],
            'zero'                                       => ['0', 0],
            'negative integer'                           => ['-42', -42],
            'PHP_INT_MAX'                                => ['9223372036854775807', PHP_INT_MAX],
            'PHP_INT_MIN'                                => ['PHP_INT_MIN', PHP_INT_MIN],
            'float'                                      => ['1.5', 1.5],
            'integral float'                             => ['1.0', 1.0],
            'negative zero'                              => ['-0.0', -0.0],
            'float with exponent'                        => ['1.0E+25', 1.0E+25],
            'decimal fraction'                           => ['0.1', 0.1],
            'NAN'                                        => ['NAN', NAN],
            'INF'                                        => ['INF', INF],
            'negative INF'                               => ['-INF', -INF],
            'empty string'                               => ["''", ''],
            'string'                                     => ["'a'", 'a'],
            'string with quote and backslash'            => ["'it\\'s a \\\\'", 'it\'s a \\'],
            'string with multibyte characters'           => ["'ä€😀'", 'ä€😀'],
            'string with newline'                        => ['"a\nb"', "a\nb"],
            'string with NUL'                            => ['"\x00"', "\0"],
            'string with escape sequences'               => ['"\t\r\v\e\f \"quoted\" \$dollar \\\\"', "\t\r\v\e\f \"quoted\" \$dollar \\"],
            'string with DEL'                            => ['"\x7F"', "\x7F"],
            'string with C1 control character'           => ['"a\u{85}ä"', "a\u{85}ä"],
            'string that is not valid UTF-8'             => ['"\xFF\x00a\xC3("', "\xFF\x00a\xC3("],
            'empty array'                                => ['[]', []],
            'list'                                       => ["[1, 'a']", [1, 'a']],
            'array with keys'                            => ["['a' => 1, 2 => [true]]", ['a' => 1, 2 => [true]]],
            'array with integer keys that is not a list' => ["[1 => 'a']", [1 => 'a']],
            'imported enumeration case'                  => ['Currency::EUR', Currency::EUR],
            'array with enumeration cases'               => ['[Currency::EUR, Currency::GBP]', [Currency::EUR, Currency::GBP]],
            'point in time'                              => ["new \\DateTimeImmutable('1970-01-01T00:00:00+00:00')", new DateTimeImmutable('@0')],
            'point in time with microseconds'            => ["new \\DateTimeImmutable('2026-10-08T12:34:56.789000', new \\DateTimeZone('Europe/Berlin'))", new DateTimeImmutable('2026-10-08 12:34:56.789 Europe/Berlin')],
            'point in time with time zone offset'        => ["new \\DateTimeImmutable('2026-10-08T12:34:56-05:00')", new DateTimeImmutable('2026-10-08T12:34:56-05:00')],
            'mutable point in time'                      => ["new \\DateTime('1970-01-01T00:00:00+00:00')", new DateTime('@0')],
            'earlier of two equal local times'           => ["(new \\DateTimeImmutable('2020-10-25T02:59:59+02:00'))->setTimezone(new \\DateTimeZone('Europe/Berlin'))", new DateTimeImmutable('2020-10-25T02:59:59+02:00')->setTimezone(new DateTimeZone('Europe/Berlin'))],
            'later of two equal local times'             => ["new \\DateTimeImmutable('2020-10-25T02:59:59', new \\DateTimeZone('Europe/Berlin'))", new DateTimeImmutable('2020-10-25T02:59:59+01:00')->setTimezone(new DateTimeZone('Europe/Berlin'))],
            'mutable, earlier of two local times'        => ["(new \\DateTime('2020-10-25T02:59:59+02:00'))->setTimezone(new \\DateTimeZone('Europe/Berlin'))", new DateTime('2020-10-25T02:59:59+02:00')->setTimezone(new DateTimeZone('Europe/Berlin'))],
        ];
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function roundTripProvider(): array
    {
        $values = [];

        foreach (self::valueProvider() as $name => [, $value]) {
            $values[$name] = [$value];
        }

        return $values;
    }

    /**
     * @return array<string, array{DateTimeInterface}>
     */
    public static function pointInTimeProvider(): array
    {
        $values = [];

        foreach (self::valueProvider() as $name => [, $value]) {
            if ($value instanceof DateTimeInterface) {
                $values[$name] = [$value];
            }
        }

        return $values;
    }

    #[DataProvider('valueProvider')]
    public function testExportsValuesAsPhpExpressions(string $expected, mixed $value): void
    {
        $this->assertSame($expected, $this->exporter(new Constructions)->export($value));
    }

    #[DataProvider('roundTripProvider')]
    public function testEvaluatingTheExportedExpressionYieldsTheValue(mixed $value): void
    {
        $scope = NameScope::forMethod(new ReflectionMethod(PropertyBasedNameResolutionClassInGlobalNamespace::class, 'method'));

        $this->assertSame(serialize($value), serialize(eval('return ' . new CounterexampleExporter($scope, new Constructions)->export($value) . ';')));
    }

    /**
     * The serialized form of a point in time has its local time and the
     * name of its time zone, which does not tell the two points in time
     * apart at which a local time occurs when daylight saving time ends.
     */
    #[DataProvider('pointInTimeProvider')]
    public function testEvaluatingTheExportedExpressionOfAPointInTimeYieldsTheSamePointInTime(DateTimeInterface $value): void
    {
        $scope    = NameScope::forMethod(new ReflectionMethod(PropertyBasedNameResolutionClassInGlobalNamespace::class, 'method'));
        $exported = eval('return ' . new CounterexampleExporter($scope, new Constructions)->export($value) . ';');

        $this->assertInstanceOf($value::class, $exported);
        $this->assertSame($value->format('U.u P e'), $exported->format('U.u P e'));
    }

    public function testExportsAnObjectThatWasCreatedWithNewAsTheExpressionThatCreatedIt(): void
    {
        [$value, $constructions] = $this->generate(Money::class, [5, 1]);

        $this->assertSame('new Money(amount: 5, currency: Currency::USD)', $this->exporter($constructions)->export($value));
    }

    public function testExportsNestedObjectsThatWereCreatedWithNew(): void
    {
        [$value, $constructions] = $this->generate(Node::class, [1, 0, 0, 0, 1, 0, 0, 0]);

        $this->assertSame(
            'new Node(left: new Node(left: null, right: null, value: 1), right: null, value: 0)',
            $this->exporter($constructions)->export($value),
        );
    }

    public function testExportsAnObjectOfAClassWithoutConstructorWithoutArguments(): void
    {
        [$value, $constructions] = $this->generate(ClassWithoutConstructor::class, []);

        $this->assertSame('new ClassWithoutConstructor', $this->exporter($constructions)->export($value));
    }

    public function testExportsAnObjectWithTheArgumentsItWasCreatedWithEvenWhenItsStateWasChanged(): void
    {
        [$value, $constructions] = $this->generate(Counter::class, [0, 3]);

        $this->assertInstanceOf(Counter::class, $value);

        $value->increment();

        $this->assertSame('new Counter(count: 3)', $this->exporter($constructions)->export($value));
    }

    public function testWritesTheFullyQualifiedNameOfAClassThatTheFileDoesNotImport(): void
    {
        [$value, $constructions] = $this->generate(ClassWithoutConstructor::class, []);

        $scope = NameScope::forMethod(new ReflectionMethod(ClassWithImports::class, 'method'));

        $this->assertSame(
            'new \PHPUnit\TestFixture\PropertyBased\Derivation\ClassWithoutConstructor',
            new CounterexampleExporter($scope, $constructions)->export($value),
        );
    }

    public function testEvaluatingTheExportedExpressionOfAnObjectThatWasCreatedWithNewYieldsAnEqualObject(): void
    {
        [$value, $constructions] = $this->generate(Money::class, [5, 2]);

        $scope = NameScope::forMethod(new ReflectionMethod(PropertyBasedNameResolutionClassInGlobalNamespace::class, 'method'));

        $this->assertEquals($value, eval('return ' . new CounterexampleExporter($scope, $constructions)->export($value) . ';'));
    }

    public function testExportsOtherObjectsWithTheExporter(): void
    {
        $this->assertStringMatchesFormat(
            'PHPUnit\TestFixture\PropertyBased\Derivation\ExampleEmailAddress Object #%d ()',
            $this->exporter(new Constructions)->export(new ExampleEmailAddress),
        );
    }

    public function testExportsValuesThatAreNeitherScalarsNorArraysNorObjectsWithTheExporter(): void
    {
        $resource = fopen('php://memory', 'r');

        $this->assertStringMatchesFormat('resource(%d) of type (stream)', $this->exporter(new Constructions)->export($resource));

        fclose($resource);
    }

    private function exporter(Constructions $constructions): CounterexampleExporter
    {
        return new CounterexampleExporter(
            NameScope::forMethod(new ReflectionMethod(Parameters::class, 'method')),
            $constructions,
        );
    }

    /**
     * @param class-string           $className
     * @param list<non-negative-int> $choices
     *
     * @return array{mixed, Constructions}
     */
    private function generate(string $className, array $choices): array
    {
        $source = ChoiceSource::replay(new ChoiceSequence($choices));

        $value = new GeneratorDeriver(GeneratorRegistry::empty())->forClass($className)->generate($source);

        return [$value, $source->constructions()];
    }
}
