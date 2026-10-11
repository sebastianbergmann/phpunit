<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\Derivation;

use function array_is_list;
use function array_keys;
use function count;
use function in_array;
use function is_array;
use function is_bool;
use function is_finite;
use function is_float;
use function is_int;
use function is_numeric;
use function is_string;
use function mb_check_encoding;
use function strtolower;
use Closure;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\PropertyBased\CannotDeriveGeneratorException;
use PHPUnit\Framework\PropertyBased\ChoiceSequence;
use PHPUnit\Framework\PropertyBased\ChoiceSource;
use PHPUnit\Framework\PropertyBased\ExampleRejectedException;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\Generator\DeferredGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ObjectGenerator;
use PHPUnit\Framework\PropertyBased\GeneratorRefersToItselfException;
use PHPUnit\Framework\PropertyBased\Generators;
use PHPUnit\Framework\PropertyBased\Type\NameScope;
use PHPUnit\Framework\PropertyBased\Type\TypeParser;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\PropertyBased\Currency;
use PHPUnit\TestFixture\PropertyBased\Derivation\ClassWithoutConstructor;
use PHPUnit\TestFixture\PropertyBased\Derivation\ClassWithVariadicConstructor;
use PHPUnit\TestFixture\PropertyBased\Derivation\Customer;
use PHPUnit\TestFixture\PropertyBased\Derivation\EmailAddress;
use PHPUnit\TestFixture\PropertyBased\Derivation\ExampleEmailAddress;
use PHPUnit\TestFixture\PropertyBased\Derivation\Expression;
use PHPUnit\TestFixture\PropertyBased\Derivation\Money;
use PHPUnit\TestFixture\PropertyBased\Derivation\Node;
use PHPUnit\TestFixture\PropertyBased\Derivation\Parameters;
use PHPUnit\TestFixture\PropertyBased\Derivation\Tree;
use Random\Engine\Xoshiro256StarStar;
use Random\Randomizer;
use ReflectionMethod;
use ReflectionParameter;

#[CoversClass(GeneratorDeriver::class)]
#[CoversClass(ObjectGenerator::class)]
#[CoversClass(DeferredGenerator::class)]
#[CoversClass(CannotDeriveGeneratorException::class)]
#[CoversClass(ExampleRejectedException::class)]
#[CoversClass(GeneratorRefersToItselfException::class)]
#[Small]
#[Group('framework')]
#[Group('framework/property-based-testing')]
final class GeneratorDeriverTest extends TestCase
{
    /**
     * @return array<string, array{string, Closure(mixed): bool, mixed}>
     */
    public static function typeProvider(): array
    {
        return [
            'int'                           => ['int', is_int(...), 0],
            'integer'                       => ['integer', is_int(...), 0],
            'integer range'                 => ['int<0, 150>', static fn (mixed $value): bool => is_int($value) && $value >= 0 && $value <= 150, 0],
            'integer range up to -1'        => ['int<min, -1>', static fn (mixed $value): bool => is_int($value) && $value < 0, -1],
            'integer range from 5'          => ['int<5, max>', static fn (mixed $value): bool => is_int($value) && $value >= 5, 5],
            'positive-int'                  => ['positive-int', static fn (mixed $value): bool => is_int($value) && $value > 0, 1],
            'negative-int'                  => ['negative-int', static fn (mixed $value): bool => is_int($value) && $value < 0, -1],
            'non-negative-int'              => ['non-negative-int', static fn (mixed $value): bool => is_int($value) && $value >= 0, 0],
            'non-positive-int'              => ['non-positive-int', static fn (mixed $value): bool => is_int($value) && $value <= 0, 0],
            'non-zero-int'                  => ['non-zero-int', static fn (mixed $value): bool => is_int($value) && $value !== 0, 1],
            'float'                         => ['float', static fn (mixed $value): bool => is_float($value) && is_finite($value), 0.0],
            'double'                        => ['double', is_float(...), 0.0],
            'bool'                          => ['bool', is_bool(...), false],
            'boolean'                       => ['boolean', is_bool(...), false],
            'true'                          => ['true', static fn (mixed $value): bool => $value === true, true],
            'false'                         => ['false', static fn (mixed $value): bool => $value === false, false],
            'null'                          => ['null', static fn (mixed $value): bool => $value === null, null],
            'string'                        => ['string', static fn (mixed $value): bool => is_string($value) && mb_check_encoding($value, 'UTF-8'), ''],
            'non-empty-string'              => ['non-empty-string', static fn (mixed $value): bool => is_string($value) && $value !== '', 'a'],
            'non-falsy-string'              => ['non-falsy-string', static fn (mixed $value): bool => is_string($value) && $value !== '' && $value !== '0', 'a'],
            'truthy-string'                 => ['truthy-string', static fn (mixed $value): bool => is_string($value) && $value !== '' && $value !== '0', 'a'],
            'numeric-string'                => ['numeric-string', static fn (mixed $value): bool => is_string($value) && is_numeric($value), '0'],
            'lowercase-string'              => ['lowercase-string', static fn (mixed $value): bool => is_string($value) && strtolower($value) === $value, ''],
            'array-key'                     => ['array-key', static fn (mixed $value): bool => is_int($value) || is_string($value), 0],
            'keyword in uppercase'          => ['INT', is_int(...), 0],
            'string literals'               => ["'a'|'b'", static fn (mixed $value): bool => in_array($value, ['a', 'b'], true), 'a'],
            'integer literal'               => ['42', static fn (mixed $value): bool => $value === 42, 42],
            'list'                          => ['list<int>', static fn (mixed $value): bool => is_array($value) && array_is_list($value), []],
            'non-empty list'                => ['non-empty-list<int>', static fn (mixed $value): bool => is_array($value) && array_is_list($value) && $value !== [], [0]],
            'list without type argument'    => ['list', static fn (mixed $value): bool => is_array($value) && array_is_list($value), []],
            'array with keys and values'    => ['array<int, string>', static fn (mixed $value): bool => is_array($value), []],
            'array with values'             => ['array<string>', static fn (mixed $value): bool => is_array($value), []],
            'non-empty array'               => ['non-empty-array<int, bool>', static fn (mixed $value): bool => is_array($value) && $value !== [], [0 => false]],
            'array'                         => ['array', is_array(...), []],
            'array suffix'                  => ['int[]', is_array(...), []],
            'array shape'                   => ['array{name: non-empty-string, age: int<0, 150>}', is_array(...), ['name' => 'a', 'age' => 0]],
            'array shape with optional key' => ['array{a: int, b?: string}', is_array(...), ['a' => 0]],
            'optional numeric-string key'   => ["array{'1'?: int, b?: int}", is_array(...), []],
            'list shape'                    => ['list{int, string}', is_array(...), [0, '']],
            'array shape without keys'      => ['array{int, bool}', is_array(...), [0, false]],
            'implicit key after int key'    => ['array{0: int, string}', static fn (mixed $value): bool => is_array($value) && is_int($value[0] ?? null) && is_string($value[1] ?? null), [0, '']],
            'implicit key after quoted key' => ["array{'0': int, string}", static fn (mixed $value): bool => is_array($value) && is_int($value[0] ?? null) && is_string($value[1] ?? null), [0, '']],
            'implicit key after 5'          => ['array{5: int, string}', static fn (mixed $value): bool => is_array($value) && is_int($value[5] ?? null) && is_string($value[6] ?? null), [5 => 0, 6 => '']],
            'nullable type'                 => ['?int', static fn (mixed $value): bool => $value === null || is_int($value), null],
            'union with null'               => ['int|null', static fn (mixed $value): bool => $value === null || is_int($value), null],
            'union'                         => ['int|string', static fn (mixed $value): bool => is_int($value) || is_string($value), 0],
            'union of null'                 => ['null|null', static fn (mixed $value): bool => $value === null, null],
            'enumeration'                   => ['Currency', static fn (mixed $value): bool => $value instanceof Currency, Currency::EUR],
            'class'                         => ['Money', static fn (mixed $value): bool => $value instanceof Money && $value->amount() >= 0, new Money(0, Currency::EUR)],
            'class without constructor'     => ['ClassWithoutConstructor', static fn (mixed $value): bool => $value instanceof ClassWithoutConstructor, new ClassWithoutConstructor],
            'DateTimeImmutable'             => ['\\DateTimeImmutable', static fn (mixed $value): bool => $value instanceof DateTimeImmutable && $value->getTimestamp() >= GeneratorDeriver::SMALLEST_TIMESTAMP && $value->getTimestamp() <= GeneratorDeriver::LARGEST_TIMESTAMP, new DateTimeImmutable('@0')],
            'DateTimeInterface'             => ['\\DateTimeInterface', static fn (mixed $value): bool => $value instanceof DateTimeImmutable, new DateTimeImmutable('@0')],
            'DateTime'                      => ['\\DateTime', static fn (mixed $value): bool => $value instanceof DateTime, new DateTime('@0')],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function unsupportedTypeProvider(): array
    {
        return [
            'mixed'                              => ['mixed', 'mixed is not supported'],
            'object'                             => ['object', 'object is not supported'],
            'callable'                           => ['callable', 'callable is not supported'],
            'iterable'                           => ['iterable', 'iterable is not supported'],
            'class-string'                       => ['class-string', 'class-string is not supported'],
            'unknown class'                      => ['Unknown', 'PHPUnit\TestFixture\PropertyBased\Derivation\Unknown is neither a supported type nor the name of an existing class'],
            'interface'                          => ['EmailAddress', 'PHPUnit\TestFixture\PropertyBased\Derivation\EmailAddress is an interface'],
            'trait'                              => ['TraitForDerivation', 'PHPUnit\TestFixture\PropertyBased\Derivation\TraitForDerivation is a trait'],
            'abstract class'                     => ['AbstractClass', 'class PHPUnit\TestFixture\PropertyBased\Derivation\AbstractClass is abstract'],
            'class without public constructor'   => ['ClassWithPrivateConstructor', 'class PHPUnit\TestFixture\PropertyBased\Derivation\ClassWithPrivateConstructor does not have a public constructor'],
            'Closure'                            => ['\Closure', 'class Closure does not have a public constructor'],
            'enumeration without cases'          => ['\PHPUnit\TestFixture\PropertyBased\EnumerationWithoutCases', 'enumeration PHPUnit\TestFixture\PropertyBased\EnumerationWithoutCases has no cases'],
            'empty integer range'                => ['int<5, 1>', 'the minimum of int<5, 1> is greater than its maximum'],
            'integer range with invalid bounds'  => ['int<max, min>', 'int<max, min> is not supported, the bounds of an integer range must be integers, min, or max'],
            'integer range with string bound'    => ["int<0, 'a'>", "int<0, 'a'> is not supported, the bounds of an integer range must be integers, min, or max"],
            'int with one type argument'         => ['int<1>', 'int<1> is not supported, int takes either no type arguments or a minimum and a maximum'],
            'list with two type arguments'       => ['list<int, string>', 'list<int, string> is not supported, list takes at most a type for its elements'],
            'array with three type arguments'    => ['array<int, string, bool>', 'array<int, string, bool> is not supported, array takes at most a type for its keys and a type for its values'],
            'keyword with type argument'         => ['string<int>', 'string<int> is not supported, string does not take type arguments'],
            'object shape'                       => ['object{a: int}', 'object{a: int} is not supported'],
            'unsupported constructor parameter'  => ['ClassWithUnsupportedConstructorParameter', 'cannot derive a generator for parameter $value of PHPUnit\TestFixture\PropertyBased\Derivation\ClassWithUnsupportedConstructorParameter::__construct() of type mixed: mixed is not supported'],
            'unparsable constructor parameter'   => ['ClassWithUnparsableConstructorParameterType', 'cannot derive a generator for parameter $callback of PHPUnit\TestFixture\PropertyBased\Derivation\ClassWithUnparsableConstructorParameterType::__construct(), the type in its @param tag cannot be parsed: unexpected "(" at offset 9, expected the name of the parameter'],
            'intersection constructor parameter' => ['ClassWithIntersectionConstructorParameter', 'cannot derive a generator for parameter $value of PHPUnit\TestFixture\PropertyBased\Derivation\ClassWithIntersectionConstructorParameter::__construct() of type Countable&Iterator: intersection type Countable&Iterator is not supported'],
        ];
    }

    /**
     * @return array<string, array{non-empty-string, mixed}>
     */
    public static function parameterProvider(): array
    {
        return [
            'type from @param tag' => ['documented', 'a'],
            'native type'          => ['native', 0],
            'nullable native type' => ['nullable', null],
            // PHP orders the members of a native union type, string before int
            'native union type'        => ['union', ''],
            'type with imported class' => ['currencies', []],
        ];
    }

    /**
     * @return array<string, array{non-empty-string, non-empty-string, string}>
     */
    public static function unsupportedParameterProvider(): array
    {
        return [
            'parameter without type' => [
                'method',
                'untyped',
                'Cannot derive a generator for parameter $untyped of type mixed: mixed is not supported',
            ],

            'parameter with intersection type' => [
                'method',
                'intersection',
                'Cannot derive a generator for parameter $intersection of type Countable&Iterator: intersection type Countable&Iterator is not supported',
            ],

            'parameter whose class cannot be constructed' => [
                'method',
                'nested',
                'Cannot derive a generator for parameter $nested of type PHPUnit\TestFixture\PropertyBased\Derivation\ClassWithUnsupportedConstructorParameter: cannot derive a generator for parameter $value of PHPUnit\TestFixture\PropertyBased\Derivation\ClassWithUnsupportedConstructorParameter::__construct() of type mixed: mixed is not supported',
            ],

            'variadic parameter' => [
                'methodWithVariadicParameter',
                'values',
                'Cannot derive a generator for parameter $values, variadic parameters are not supported',
            ],

            'parameter with type that cannot be parsed' => [
                'methodWithUnparsableParameterType',
                'values',
                'Cannot derive a generator for parameter $values, the type in its @param tag cannot be parsed: unexpected "$values" at offset 10, expected ">"',
            ],
        ];
    }

    /**
     * @return array<string, array{non-empty-string}>
     */
    public static function classThatIsRecursiveThroughAUnionOrANullableTypeProvider(): array
    {
        return [
            'list of a union'         => ['TreeWithUnionChildren'],
            'list of a nullable type' => ['TreeWithNullableChildren'],
            'union without null'      => ['Expression'],
            'union of null and class' => ['LinkedList'],
        ];
    }

    /**
     * @param Closure(mixed): bool $isValid
     */
    #[DataProvider('typeProvider')]
    public function testDerivesGeneratorsFromTypes(string $type, Closure $isValid, mixed $simplestValue): void
    {
        $generator = $this->derive($type);

        foreach ($this->values($generator, 200) as $value) {
            $this->assertTrue($isValid($value));
        }

        $this->assertEquals($simplestValue, $this->replay($generator, []));
    }

    public function testGeneratesTheBoundariesOfTheRangeOfPointsInTime(): void
    {
        $generator = $this->derive('\\DateTimeImmutable');

        $this->assertEquals(new DateTimeImmutable('2199-12-31 23:59:59 UTC'), $this->replay($generator, [0, GeneratorDeriver::LARGEST_TIMESTAMP]));
        $this->assertEquals(new DateTimeImmutable('1900-01-01 00:00:00 UTC'), $this->replay($generator, [1, -GeneratorDeriver::SMALLEST_TIMESTAMP - 1]));
    }

    #[DataProvider('unsupportedTypeProvider')]
    public function testReportsTypesForWhichNoGeneratorCanBeDerived(string $type, string $message): void
    {
        $this->expectException(CannotDeriveGeneratorException::class);
        $this->expectExceptionMessage($message);

        $this->derive($type);
    }

    /**
     * @param non-empty-string $name
     */
    #[DataProvider('parameterProvider')]
    public function testDerivesGeneratorsForParameters(string $name, mixed $simplestValue): void
    {
        $generator = $this->deriver()->forParameter(new ReflectionParameter([Parameters::class, 'method'], $name));

        $this->assertSame($simplestValue, $this->replay($generator, []));
    }

    public function testDerivesTheGeneratorForAParameterWithTheClassesImportedInTheFileThatDeclaresTheMethod(): void
    {
        $generator = $this->deriver()->forParameter(new ReflectionParameter([Parameters::class, 'method'], 'currencies'));

        $this->assertSame([Currency::USD], $this->replay($generator, [1, 1, 0]));
    }

    /**
     * @param non-empty-string $method
     * @param non-empty-string $parameter
     */
    #[DataProvider('unsupportedParameterProvider')]
    public function testReportsParametersForWhichNoGeneratorCanBeDerived(string $method, string $parameter, string $message): void
    {
        $this->expectException(CannotDeriveGeneratorException::class);
        $this->expectExceptionMessage($message);

        $this->deriver()->forParameter(new ReflectionParameter([Parameters::class, $method], $parameter));
    }

    public function testVariadicParametersOfAConstructorReceiveNoArguments(): void
    {
        $value = $this->replay($this->derive('ClassWithVariadicConstructor'), [0, 5]);

        $this->assertInstanceOf(ClassWithVariadicConstructor::class, $value);
        $this->assertSame(5, $value->first());
        $this->assertSame([], $value->rest());
    }

    public function testGeneratesObjectsOfRecursiveClasses(): void
    {
        $generator = $this->derive('Node');
        $values    = $this->values($generator, 200);

        $withChildren = 0;

        foreach ($values as $value) {
            $this->assertInstanceOf(Node::class, $value);

            if ($value->left() !== null || $value->right() !== null) {
                $withChildren++;
            }
        }

        $this->assertGreaterThan(0, $withChildren);
        $this->assertEquals(new Node(null, null, 0), $this->replay($generator, []));
    }

    public function testGeneratesObjectsOfClassesThatAreRecursiveThroughALists(): void
    {
        $generator = $this->derive('Tree');

        $withChildren = 0;

        foreach ($this->values($generator, 200) as $value) {
            $this->assertInstanceOf(Tree::class, $value);

            if ($value->children() !== []) {
                $withChildren++;
            }
        }

        $this->assertGreaterThan(0, $withChildren);
    }

    /**
     * @param non-empty-string $className
     */
    #[DataProvider('classThatIsRecursiveThroughAUnionOrANullableTypeProvider')]
    public function testKeepsObjectsOfClassesThatAreRecursiveThroughAUnionOrANullableTypeSmall(string $className): void
    {
        $generator  = $this->derive($className);
        $randomizer = new Randomizer(new Xoshiro256StarStar(1));
        $rejected   = 0;

        for ($i = 0; $i < 200; $i++) {
            try {
                $generator->generate(ChoiceSource::random($randomizer));
            } catch (ExampleRejectedException) {
                $rejected++;
            }
        }

        $this->assertLessThan(10, $rejected);
    }

    public function testTheMembersOfAUnionThatDoNotReferToAClassThatIsBeingDerivedAreTheSimplest(): void
    {
        $this->assertEquals(new Expression(0, 0), $this->replay($this->derive('Expression'), []));
    }

    public function testRejectsTheExampleWhenTooManyObjectsOfTheSameClassAreNested(): void
    {
        $this->expectException(ExampleRejectedException::class);
        $this->expectExceptionMessage('Generating the example required more than 5 nested objects of class PHPUnit\TestFixture\PropertyBased\Derivation\Node');

        // choosing a left node, rather than null, for every node
        $this->replay($this->derive('Node'), [1, 1, 1, 1, 1, 1, 1, 1]);
    }

    public function testTheKeysOfAnArrayWithStringKeysAreStrings(): void
    {
        foreach ($this->values($this->derive('array<string, int>'), 500) as $value) {
            $this->assertIsArray($value);

            foreach (array_keys($value) as $key) {
                $this->assertIsString($key);
            }
        }
    }

    public function testUsesTheGeneratorRegisteredForAClass(): void
    {
        $emailAddress = new ExampleEmailAddress;
        $deriver      = new GeneratorDeriver(GeneratorRegistry::from([EmailAddress::class => Generators::constant($emailAddress)]));

        $customer = $this->replay($deriver->forClass(Customer::class), [], $deriver);

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertSame($emailAddress, $customer->emailAddress());
    }

    public function testTheGeneratorRegisteredForAClassTakesPrecedenceOverTheDerivedOne(): void
    {
        $money   = new Money(42, Currency::GBP);
        $deriver = new GeneratorDeriver(GeneratorRegistry::from([Money::class => Generators::constant($money)]));

        $this->assertSame($money, $this->replay($deriver->forType((new TypeParser)->parse('\\' . Money::class), $this->scope()), [], $deriver));
    }

    public function testReportsAGeneratorRegisteredForAClassThatRefersToItselfBeforeItMakesAChoice(): void
    {
        $deriver = new GeneratorDeriver(
            GeneratorRegistry::from(
                [
                    Money::class => Generators::objects(Money::class)->map(static fn (Money $money): Money => $money),
                ],
            ),
        );

        $this->expectException(GeneratorRefersToItselfException::class);
        $this->expectExceptionMessage('Cannot generate objects of class ' . Money::class . ': the generator refers to itself before it makes a choice');

        $this->replay($deriver->forClass(Money::class), [], $deriver);
    }

    public function testAGeneratorRegisteredForAClassMayReferToItselfAfterItMadeAChoice(): void
    {
        $deriver = new GeneratorDeriver(
            GeneratorRegistry::from(
                [
                    Money::class => Generators::nullable(Generators::objects(Money::class), true)->map(
                        static fn (?Money $money): Money => $money ?? new Money(0, Currency::EUR),
                    ),
                ],
            ),
        );

        $this->assertEquals(new Money(0, Currency::EUR), $this->replay($deriver->forClass(Money::class), [1, 1, 0], $deriver));
    }

    public function testDerivesTheGeneratorForAClassOnlyOnce(): void
    {
        $deriver = $this->deriver();

        $this->assertSame($deriver->forClass(Money::class), $deriver->forClass(Money::class));
    }

    public function testDerivesADeferredGeneratorOnlyOnce(): void
    {
        $deriver   = $this->deriver();
        $generator = new DeferredGenerator(static fn (GeneratorDeriver $deriver): AbstractGenerator => $deriver->forClass(Money::class), 'money');

        $this->assertSame($deriver->deferred($generator), $deriver->deferred($generator));
    }

    /**
     * @return AbstractGenerator<mixed>
     */
    private function derive(string $type): AbstractGenerator
    {
        return $this->deriver()->forType((new TypeParser)->parse($type), $this->scope());
    }

    private function deriver(): GeneratorDeriver
    {
        return new GeneratorDeriver(GeneratorRegistry::empty());
    }

    private function scope(): NameScope
    {
        return NameScope::forMethod(new ReflectionMethod(Parameters::class, 'method'));
    }

    /**
     * @param AbstractGenerator<mixed> $generator
     * @param list<non-negative-int>   $choices
     */
    private function replay(AbstractGenerator $generator, array $choices, ?GeneratorDeriver $deriver = null): mixed
    {
        return $generator->generate(ChoiceSource::replay(new ChoiceSequence($choices), ChoiceSource::DEFAULT_MAXIMUM_NUMBER_OF_CHOICES, $deriver));
    }

    /**
     * @param AbstractGenerator<mixed> $generator
     * @param positive-int             $count
     *
     * @return list<mixed>
     */
    private function values(AbstractGenerator $generator, int $count): array
    {
        $randomizer = new Randomizer(new Xoshiro256StarStar(1));
        $values     = [];

        for ($attempt = 0; $attempt < 10 * $count && count($values) < $count; $attempt++) {
            try {
                $values[] = $generator->generate(ChoiceSource::random($randomizer));
            } catch (ExampleRejectedException) {
                continue;
            }
        }

        $this->assertCount($count, $values);

        return $values;
    }
}
