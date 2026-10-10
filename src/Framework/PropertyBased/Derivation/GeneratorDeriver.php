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

use const PHP_FLOAT_MAX;
use const PHP_INT_MAX;
use const PHP_INT_MIN;
use function array_key_first;
use function assert;
use function class_exists;
use function count;
use function enum_exists;
use function in_array;
use function interface_exists;
use function is_int;
use function is_string;
use function ltrim;
use function sprintf;
use function strtolower;
use function trait_exists;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\PropertyBased\CannotDeriveGeneratorException;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ArrayGenerator;
use PHPUnit\Framework\PropertyBased\Generator\BooleanGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ConstantGenerator;
use PHPUnit\Framework\PropertyBased\Generator\DeferredGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ElementsGenerator;
use PHPUnit\Framework\PropertyBased\Generator\FilteredGenerator;
use PHPUnit\Framework\PropertyBased\Generator\FloatGenerator;
use PHPUnit\Framework\PropertyBased\Generator\IntegerGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ListGenerator;
use PHPUnit\Framework\PropertyBased\Generator\MappedGenerator;
use PHPUnit\Framework\PropertyBased\Generator\NullableGenerator;
use PHPUnit\Framework\PropertyBased\Generator\ObjectGenerator;
use PHPUnit\Framework\PropertyBased\Generator\OneOfGenerator;
use PHPUnit\Framework\PropertyBased\Generator\RecordGenerator;
use PHPUnit\Framework\PropertyBased\Generator\Repetition;
use PHPUnit\Framework\PropertyBased\Generator\StringGenerator;
use PHPUnit\Framework\PropertyBased\InvalidTypeException;
use PHPUnit\Framework\PropertyBased\Type\ArrayShapeType;
use PHPUnit\Framework\PropertyBased\Type\DocComments;
use PHPUnit\Framework\PropertyBased\Type\LiteralType;
use PHPUnit\Framework\PropertyBased\Type\NamedType;
use PHPUnit\Framework\PropertyBased\Type\NameScope;
use PHPUnit\Framework\PropertyBased\Type\NullableType;
use PHPUnit\Framework\PropertyBased\Type\ParameterTypes;
use PHPUnit\Framework\PropertyBased\Type\Type;
use PHPUnit\Framework\PropertyBased\Type\UnionType;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use WeakMap;

/**
 * Derives generators from the types of parameters: from the type in the
 * parameter's @param tag (including @phpstan-param and @psalm-param) if there
 * is one, and from its native type declaration otherwise. The @param tag is
 * read from the source file when OPcache has discarded the doc comment.
 *
 * A generator that the test case class registers for a class with
 * #[GeneratorFor] is used for every value of exactly that class, including
 * the arguments for the constructors of other classes.
 *
 * Objects of other concrete classes are created with new, using arguments
 * that are generated for the parameters of the constructor, recursively.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class GeneratorDeriver
{
    /**
     * The points in time from 1900-01-01 00:00:00 to 2199-12-31 23:59:59 UTC,
     * the simplest of which is the Unix epoch.
     */
    public const int SMALLEST_TIMESTAMP = -2208988800;

    public const int LARGEST_TIMESTAMP = 7258118399;

    /**
     * The probability that a nullable value or a collection that can contain
     * an object of a class that is being constructed contains it, which is
     * smaller than usual so that the recursive construction ends soon. The
     * collections and nullable values that are created with Generators for
     * recursive values use it, too.
     */
    public const float PROBABILITY_OF_RECURSION = 0.25;

    /**
     * @var list<lowercase-string>
     */
    private const array UNSUPPORTED_KEYWORDS = [
        'mixed',
        'object',
        'callable',
        'iterable',
        'resource',
        'void',
        'never',
        'scalar',
        'numeric',
        'class-string',
        'callable-string',
        'literal-string',
        'non-empty-literal-string',
        'key-of',
        'value-of',
    ];
    private readonly GeneratorRegistry $registry;

    /**
     * @var array<lowercase-string, AbstractGenerator<mixed>>
     */
    private array $classes = [];

    /**
     * @var array<lowercase-string, true>
     */
    private array $classesBeingDerived = [];

    /**
     * @var WeakMap<DeferredGenerator<mixed>, AbstractGenerator<mixed>>
     */
    private WeakMap $deferred;

    /**
     * How often the generator of a class that is being derived was referred
     * to from within the generators for the arguments of its constructor.
     *
     * @var non-negative-int
     */
    private int $numberOfRecursiveReferences = 0;

    /**
     * The probability that a collection gets another element, which is
     * PROBABILITY_OF_RECURSION when its elements can contain values of the
     * generator for the collection, so that the generated values stay small.
     *
     * @param bool $recursive whether the generator for the elements refers to a class that is being derived, directly or nested in other types such as a union or a collection, or to the generator for the collection
     */
    public static function probabilityOfAnotherElement(bool $recursive): float
    {
        if ($recursive) {
            return self::PROBABILITY_OF_RECURSION;
        }

        return Repetition::DEFAULT_PROBABILITY_OF_ANOTHER_ELEMENT;
    }

    public function __construct(GeneratorRegistry $registry)
    {
        $this->registry = $registry;
        $this->deferred = new WeakMap;
    }

    /**
     * Derives the generator for a parameter of a property.
     *
     * @throws CannotDeriveGeneratorException
     *
     * @return AbstractGenerator<mixed>
     */
    public function forParameter(ReflectionParameter $parameter): AbstractGenerator
    {
        if ($parameter->isVariadic()) {
            throw CannotDeriveGeneratorException::variadicParameter($parameter);
        }

        $method = $parameter->getDeclaringFunction();

        assert($method instanceof ReflectionMethod);

        try {
            [$type, $declaration] = $this->typeOf($parameter, ParameterTypes::fromDocComment(DocComments::of($method)));
        } catch (InvalidTypeException $e) {
            throw CannotDeriveGeneratorException::unparsableParameterType($parameter, $e);
        } catch (CannotDeriveGeneratorException $e) {
            throw CannotDeriveGeneratorException::forParameter($parameter, (string) $parameter->getType(), $e);
        }

        try {
            return $this->forType($type, NameScope::forMethod($method));
        } catch (CannotDeriveGeneratorException $e) {
            throw CannotDeriveGeneratorException::forParameter($parameter, $declaration, $e);
        }
    }

    /**
     * @throws CannotDeriveGeneratorException
     *
     * @return AbstractGenerator<mixed>
     */
    public function forType(Type $type, NameScope $scope): AbstractGenerator
    {
        if ($type instanceof NullableType) {
            $numberOfRecursiveReferences = $this->numberOfRecursiveReferences;
            $generator                   = $this->forType($type->type(), $scope);

            return $this->nullable($generator, $this->numberOfRecursiveReferences > $numberOfRecursiveReferences);
        }

        if ($type instanceof UnionType) {
            return $this->union($type, $scope);
        }

        if ($type instanceof LiteralType) {
            return new ConstantGenerator($type->value());
        }

        if ($type instanceof ArrayShapeType) {
            return $this->shape($type, $scope);
        }

        assert($type instanceof NamedType);

        return $this->named($type, $scope);
    }

    /**
     * Derives the generator for a class: the one registered for it, the one
     * for points in time or the cases of an enumeration, or one that creates
     * objects of the class with new.
     *
     * @param non-empty-string $className
     *
     * @throws CannotDeriveGeneratorException
     *
     * @return AbstractGenerator<mixed>
     */
    public function forClass(string $className): AbstractGenerator
    {
        $className = ltrim($className, '\\');

        if ($this->registry->has($className)) {
            return $this->registry->get($className);
        }

        $key = strtolower($className);

        if ($key === 'datetimeimmutable' || $key === 'datetimeinterface') {
            return $this->pointsInTime(false);
        }

        if ($key === 'datetime') {
            return $this->pointsInTime(true);
        }

        if (enum_exists($className)) {
            $cases = $className::cases();

            if ($cases === []) {
                throw CannotDeriveGeneratorException::enumerationWithoutCases($className);
            }

            return new ElementsGenerator($cases);
        }

        if (interface_exists($className)) {
            throw CannotDeriveGeneratorException::interfaceType($className);
        }

        if (trait_exists($className)) {
            throw CannotDeriveGeneratorException::traitType($className);
        }

        if (!class_exists($className)) {
            throw CannotDeriveGeneratorException::unknownType($className);
        }

        return $this->objects($className);
    }

    /**
     * Returns the generator that a deferred generator is derived as with this
     * deriver, deriving it the first time.
     *
     * @template T
     *
     * @param DeferredGenerator<T> $generator
     *
     * @throws CannotDeriveGeneratorException
     *
     * @return AbstractGenerator<T>
     */
    public function deferred(DeferredGenerator $generator): AbstractGenerator
    {
        if (!isset($this->deferred[$generator])) {
            $this->deferred[$generator] = $generator->derive($this);
        }

        /** @var AbstractGenerator<T> */
        return $this->deferred[$generator];
    }

    /**
     * @param class-string $className
     *
     * @throws CannotDeriveGeneratorException
     *
     * @return AbstractGenerator<mixed>
     */
    private function objects(string $className): AbstractGenerator
    {
        $key = strtolower($className);

        if (isset($this->classes[$key])) {
            return $this->classes[$key];
        }

        if (isset($this->classesBeingDerived[$key])) {
            $this->numberOfRecursiveReferences++;

            return new DeferredGenerator(
                static fn (self $deriver): AbstractGenerator => $deriver->forClass($className),
                sprintf('objects of class %s', $className),
            );
        }

        $class = new ReflectionClass($className);

        if ($class->isAbstract()) {
            throw CannotDeriveGeneratorException::abstractClass($className);
        }

        $constructor = $class->getConstructor();

        if (!$class->isInstantiable() || ($constructor !== null && !$constructor->isPublic())) {
            throw CannotDeriveGeneratorException::noPublicConstructor($className);
        }

        $this->classesBeingDerived[$key] = true;

        try {
            $arguments = [];

            if ($constructor !== null) {
                $arguments = $this->constructorArguments($constructor);
            }
        } finally {
            unset($this->classesBeingDerived[$key]);
        }

        $generator = new ObjectGenerator($className, $arguments);

        $this->classes[$key] = $generator;

        return $generator;
    }

    /**
     * Variadic parameters of the constructor receive no arguments.
     *
     * @throws CannotDeriveGeneratorException
     *
     * @return array<non-empty-string, AbstractGenerator<mixed>>
     */
    private function constructorArguments(ReflectionMethod $constructor): array
    {
        $scope     = NameScope::forMethod($constructor);
        $docTypes  = ParameterTypes::fromDocComment(DocComments::of($constructor));
        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            if ($parameter->isVariadic()) {
                continue;
            }

            try {
                [$type, $declaration] = $this->typeOf($parameter, $docTypes);
            } catch (InvalidTypeException $e) {
                throw CannotDeriveGeneratorException::unparsableConstructorParameterType($parameter, $e);
            } catch (CannotDeriveGeneratorException $e) {
                throw CannotDeriveGeneratorException::forConstructorParameter($parameter, (string) $parameter->getType(), $e);
            }

            try {
                $arguments[$parameter->getName()] = $this->forType($type, $scope);
            } catch (CannotDeriveGeneratorException $e) {
                throw CannotDeriveGeneratorException::forConstructorParameter($parameter, $declaration, $e);
            }
        }

        return $arguments;
    }

    /**
     * @throws CannotDeriveGeneratorException
     *
     * @return AbstractGenerator<mixed>
     */
    private function named(NamedType $type, NameScope $scope): AbstractGenerator
    {
        $keyword   = strtolower($type->name());
        $arguments = $type->arguments();

        switch ($keyword) {
            case 'int':
                if (count($arguments) === 2) {
                    return $this->integerRange($type);
                }

                if ($arguments !== []) {
                    throw CannotDeriveGeneratorException::invalidTypeArguments($type->asString(), 'int takes either no type arguments or a minimum and a maximum');
                }

                return new IntegerGenerator(PHP_INT_MIN, PHP_INT_MAX);

            case 'array':
            case 'non-empty-array':
                return $this->arrays($type, $scope, $keyword === 'non-empty-array');

            case 'list':
            case 'non-empty-list':
                return $this->lists($type, $scope, $keyword === 'non-empty-list');
        }

        $generator = $this->keyword($keyword);

        if ($generator === null) {
            return $this->classOrUnsupportedKeyword($type, $scope);
        }

        if ($arguments !== []) {
            throw CannotDeriveGeneratorException::invalidTypeArguments($type->asString(), sprintf('%s does not take type arguments', $type->name()));
        }

        return $generator;
    }

    /**
     * Returns the generator for a keyword that does not take type arguments,
     * or null when $keyword is not such a keyword.
     *
     * @return ?AbstractGenerator<mixed>
     */
    private function keyword(string $keyword): ?AbstractGenerator
    {
        return match ($keyword) {
            'integer'                           => new IntegerGenerator(PHP_INT_MIN, PHP_INT_MAX),
            'positive-int'                      => new IntegerGenerator(1, PHP_INT_MAX),
            'negative-int'                      => new IntegerGenerator(PHP_INT_MIN, -1),
            'non-negative-int'                  => new IntegerGenerator(0, PHP_INT_MAX),
            'non-positive-int'                  => new IntegerGenerator(PHP_INT_MIN, 0),
            'non-zero-int'                      => new OneOfGenerator([new IntegerGenerator(1, PHP_INT_MAX), new IntegerGenerator(PHP_INT_MIN, -1)]),
            'float', 'double'                   => new FloatGenerator(-PHP_FLOAT_MAX, PHP_FLOAT_MAX, false, false),
            'bool', 'boolean'                   => new BooleanGenerator,
            'true'                              => new ConstantGenerator(true),
            'false'                             => new ConstantGenerator(false),
            'null'                              => new ConstantGenerator(null),
            'string'                            => new StringGenerator(null, 0, null),
            'non-empty-string'                  => new StringGenerator(null, 1, null),
            'non-falsy-string', 'truthy-string' => new FilteredGenerator(
                new StringGenerator(null, 1, null),
                static fn (string $string): bool => $string !== '0',
            ),
            'numeric-string' => new OneOfGenerator(
                [
                    new MappedGenerator(new IntegerGenerator(PHP_INT_MIN, PHP_INT_MAX), static fn (int $value): string => (string) $value),
                    new MappedGenerator(new FloatGenerator(-PHP_FLOAT_MAX, PHP_FLOAT_MAX, false, false), static fn (float $value): string => (string) $value),
                ],
            ),
            'lowercase-string' => new MappedGenerator(new StringGenerator(null, 0, null), strtolower(...)),
            'array-key'        => $this->arrayKeys(),
            default            => null,
        };
    }

    /**
     * @throws CannotDeriveGeneratorException
     *
     * @return AbstractGenerator<mixed>
     */
    private function classOrUnsupportedKeyword(NamedType $type, NameScope $scope): AbstractGenerator
    {
        if (in_array(strtolower($type->name()), self::UNSUPPORTED_KEYWORDS, true)) {
            throw CannotDeriveGeneratorException::unsupportedType($type->name());
        }

        return $this->forClass($scope->resolve($type->name()));
    }

    /**
     * @throws CannotDeriveGeneratorException
     *
     * @return AbstractGenerator<int>
     */
    private function integerRange(NamedType $type): AbstractGenerator
    {
        $bounds = [];

        foreach ($type->arguments() as $index => $argument) {
            if ($argument instanceof LiteralType && is_int($argument->value())) {
                $bounds[] = $argument->value();

                continue;
            }

            if ($argument instanceof NamedType && $argument->arguments() === [] && strtolower($argument->name()) === 'min' && $index === 0) {
                $bounds[] = PHP_INT_MIN;

                continue;
            }

            if ($argument instanceof NamedType && $argument->arguments() === [] && strtolower($argument->name()) === 'max' && $index === 1) {
                $bounds[] = PHP_INT_MAX;

                continue;
            }

            throw CannotDeriveGeneratorException::invalidTypeArguments($type->asString(), 'the bounds of an integer range must be integers, min, or max');
        }

        assert(isset($bounds[0], $bounds[1]));

        [$minimum, $maximum] = $bounds;

        if ($minimum > $maximum) {
            throw CannotDeriveGeneratorException::invalidIntegerRange($type->asString());
        }

        return new IntegerGenerator($minimum, $maximum);
    }

    /**
     * Without type arguments, the keys are integers or strings and the values
     * are integers, strings, booleans, floating-point numbers, or null.
     *
     * @throws CannotDeriveGeneratorException
     *
     * @return AbstractGenerator<array<array-key, mixed>>
     */
    private function arrays(NamedType $type, NameScope $scope, bool $nonEmpty): AbstractGenerator
    {
        $arguments = $type->arguments();

        if (count($arguments) > 2) {
            throw CannotDeriveGeneratorException::invalidTypeArguments($type->asString(), sprintf('%s takes at most a type for its keys and a type for its values', $type->name()));
        }

        $numberOfRecursiveReferences = $this->numberOfRecursiveReferences;
        $keys                        = $this->arrayKeys();

        if (count($arguments) === 2) {
            $keys = $this->forType($arguments[0], $scope);
        }

        $values = $this->values();

        if ($arguments !== []) {
            $values = $this->forType($arguments[count($arguments) - 1], $scope);
        }

        return new ArrayGenerator($keys, $values, $this->minimumSize($nonEmpty), null, self::probabilityOfAnotherElement($this->numberOfRecursiveReferences > $numberOfRecursiveReferences));
    }

    /**
     * @throws CannotDeriveGeneratorException
     *
     * @return AbstractGenerator<list<mixed>>
     */
    private function lists(NamedType $type, NameScope $scope, bool $nonEmpty): AbstractGenerator
    {
        $arguments = $type->arguments();

        if (count($arguments) > 1) {
            throw CannotDeriveGeneratorException::invalidTypeArguments($type->asString(), sprintf('%s takes at most a type for its elements', $type->name()));
        }

        $numberOfRecursiveReferences = $this->numberOfRecursiveReferences;
        $elements                    = $this->values();

        if ($arguments !== []) {
            $elements = $this->forType($arguments[0], $scope);
        }

        return new ListGenerator($elements, $this->minimumSize($nonEmpty), null, self::probabilityOfAnotherElement($this->numberOfRecursiveReferences > $numberOfRecursiveReferences));
    }

    /**
     * Optional keys are sometimes omitted. An unsealed shape is generated
     * without keys that are not listed.
     *
     * @throws CannotDeriveGeneratorException
     *
     * @return AbstractGenerator<array<array-key, mixed>>
     */
    private function shape(ArrayShapeType $type, NameScope $scope): AbstractGenerator
    {
        $name = strtolower($type->name());

        if ($name !== 'array' && $name !== 'list') {
            throw CannotDeriveGeneratorException::unsupportedType($type->asString());
        }

        $generators   = [];
        $optionalKeys = [];
        $nextIndex    = 0;

        foreach ($type->items() as $item) {
            $key = $item->key();

            // like in PHPStan, an item without a key has the integer key that
            // follows the largest integer key so far, and a string key that
            // PHP stores as an integer, such as '1', is an integer key
            if ($key === null) {
                $key = $nextIndex;
            } elseif (is_string($key) && is_int(array_key_first([$key => true]))) {
                $key = (int) $key;
            }

            if (is_int($key) && $key >= $nextIndex) {
                $nextIndex = $key + 1;
            }

            $generators[$key] = $this->forType($item->type(), $scope);

            if ($item->isOptional()) {
                $optionalKeys[] = $key;
            }
        }

        return new RecordGenerator($generators, $optionalKeys);
    }

    /**
     * A union that includes null is generated as null or a value of one of
     * the other types, so that null is the simplest value. Otherwise, the
     * values of the first type are the simplest ones.
     *
     * @throws CannotDeriveGeneratorException
     *
     * @return AbstractGenerator<mixed>
     */
    private function union(UnionType $type, NameScope $scope): AbstractGenerator
    {
        $numberOfRecursiveReferences = $this->numberOfRecursiveReferences;
        $generators                  = [];
        $nullable                    = false;

        foreach ($type->types() as $member) {
            if ($member instanceof NamedType && $member->arguments() === [] && strtolower($member->name()) === 'null') {
                $nullable = true;

                continue;
            }

            $generators[] = $this->forType($member, $scope);
        }

        if ($generators === []) {
            return new ConstantGenerator(null);
        }

        if (count($generators) === 1) {
            $generator = $generators[0];
        } else {
            $generator = new OneOfGenerator($generators);
        }

        if ($nullable) {
            return $this->nullable($generator, $this->numberOfRecursiveReferences > $numberOfRecursiveReferences);
        }

        return $generator;
    }

    /**
     * @template T
     *
     * @param AbstractGenerator<T> $generator
     * @param bool                 $recursive whether $generator refers to a class that is being derived, directly or nested in other types such as a union or a collection
     *
     * @return AbstractGenerator<?T>
     */
    private function nullable(AbstractGenerator $generator, bool $recursive): AbstractGenerator
    {
        if ($recursive) {
            return new NullableGenerator($generator, self::PROBABILITY_OF_RECURSION);
        }

        return new NullableGenerator($generator);
    }

    /**
     * @return non-negative-int
     */
    private function minimumSize(bool $nonEmpty): int
    {
        if ($nonEmpty) {
            return 1;
        }

        return 0;
    }

    /**
     * @return AbstractGenerator<int|string>
     */
    private function arrayKeys(): AbstractGenerator
    {
        return new OneOfGenerator([new IntegerGenerator(PHP_INT_MIN, PHP_INT_MAX), new StringGenerator(null, 0, null)]);
    }

    /**
     * @return AbstractGenerator<null|bool|float|int|string>
     */
    private function values(): AbstractGenerator
    {
        return new OneOfGenerator(
            [
                new IntegerGenerator(PHP_INT_MIN, PHP_INT_MAX),
                new StringGenerator(null, 0, null),
                new BooleanGenerator,
                new FloatGenerator(-PHP_FLOAT_MAX, PHP_FLOAT_MAX, false, false),
                new ConstantGenerator(null),
            ],
        );
    }

    /**
     * Generates the points in time from SMALLEST_TIMESTAMP to
     * LARGEST_TIMESTAMP in UTC, the simplest of which is the Unix epoch.
     *
     * @return AbstractGenerator<DateTime>|AbstractGenerator<DateTimeImmutable>
     */
    private function pointsInTime(bool $mutable): AbstractGenerator
    {
        $timestamps = new IntegerGenerator(self::SMALLEST_TIMESTAMP, self::LARGEST_TIMESTAMP);

        if ($mutable) {
            return new MappedGenerator($timestamps, static fn (int $timestamp): DateTime => new DateTime('@' . $timestamp));
        }

        return new MappedGenerator($timestamps, static fn (int $timestamp): DateTimeImmutable => new DateTimeImmutable('@' . $timestamp));
    }

    /**
     * Returns the type of the parameter, from its @param tag or from its
     * native type declaration, together with the type as it is declared.
     *
     * @throws CannotDeriveGeneratorException
     * @throws InvalidTypeException
     *
     * @return array{Type, string}
     */
    private function typeOf(ReflectionParameter $parameter, ParameterTypes $docTypes): array
    {
        $type = $docTypes->typeOf($parameter->getName());

        if ($type !== null) {
            return [$type, $type->asString()];
        }

        $native = $parameter->getType();

        if ($native === null) {
            return [new NamedType('mixed'), 'mixed'];
        }

        return [$this->nativeType($native), (string) $native];
    }

    /**
     * @throws CannotDeriveGeneratorException
     */
    private function nativeType(ReflectionType $type): Type
    {
        if ($type instanceof ReflectionIntersectionType) {
            throw CannotDeriveGeneratorException::intersectionType((string) $type);
        }

        if ($type instanceof ReflectionUnionType) {
            $members = [];

            foreach ($type->getTypes() as $member) {
                $members[] = $this->nativeType($member);
            }

            assert($members !== []);

            return new UnionType($members);
        }

        assert($type instanceof ReflectionNamedType);

        $name = $type->getName();

        assert($name !== '');

        if (!$type->isBuiltin() && !in_array(strtolower($name), ['self', 'static', 'parent'], true)) {
            $name = '\\' . $name;
        }

        $named = new NamedType($name);

        if ($type->allowsNull() && $name !== 'null' && $name !== 'mixed') {
            return new NullableType($named);
        }

        return $named;
    }
}
