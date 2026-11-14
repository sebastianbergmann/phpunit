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
use function array_sum;
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
use function str_contains;
use function strtolower;
use function trait_exists;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
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
use PHPUnit\Framework\PropertyBased\Generator\StringGenerator;
use PHPUnit\Framework\PropertyBased\Generators;
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
use SebastianBergmann\ChoiceSequence\BernoulliDistribution;
use SebastianBergmann\ChoiceSequence\Repetition;
use WeakMap;

/**
 * Derives generators from the types of parameters: from the type in the
 * parameter's @param tag (including @phpstan-param and @psalm-param) if there
 * is one, from the type in the @var tag (including @phpstan-var and @psalm-var)
 * of the doc comment of a promoted constructor parameter that has no @param
 * tag, and from its native type declaration otherwise. The tags are read from
 * the source file when OPcache has discarded the doc comments.
 *
 * A generator that the test case class registers for a class with
 * #[GeneratorFor] is used for every value of exactly that class, including
 * the arguments for the constructors of other classes.
 *
 * Objects of other concrete classes are created with new, using arguments
 * that are generated for the parameters of the constructor, recursively. A
 * parameter that has a default value does not receive an argument when no
 * generator can be derived for its type.
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

    /**
     * The keywords of PHPDoc types that PHP does not reserve, so that a class
     * can have one of them as its name. Like in PHPStan, such a name refers
     * to the class when a class of that name exists in the namespace, or is
     * imported in the file, of the doc comment. A class in the namespace has
     * to be written as it is declared, Resource rather than resource, for
     * instance, as only the name of an imported class is resolved to the
     * name with which it is declared. The names that PHP reserves, such as
     * int and iterable, and the ones that contain a hyphen, such as
     * positive-int, cannot be the names of classes.
     *
     * @var list<lowercase-string>
     */
    private const array KEYWORDS_THAT_CAN_BE_CLASS_NAMES = [
        'integer',
        'double',
        'boolean',
        'resource',
        'scalar',
        'numeric',
    ];

    /**
     * The keywords of the types whose values are integers or strings, which
     * are the only values that PHP uses as the keys of an array as they are.
     *
     * @var list<lowercase-string>
     */
    private const array KEYWORDS_OF_ARRAY_KEYS = [
        'int',
        'integer',
        'positive-int',
        'negative-int',
        'non-negative-int',
        'non-positive-int',
        'non-zero-int',
        'string',
        'non-empty-string',
        'non-falsy-string',
        'truthy-string',
        'numeric-string',
        'lowercase-string',
        'array-key',
    ];
    private readonly GeneratorRegistry $registry;

    /**
     * @var array<lowercase-string, AbstractGenerator<mixed>>
     */
    private array $classes = [];

    /**
     * The classes that were being derived, and referred to from within the
     * generators for the arguments of its constructor, when the generator of
     * a class was derived, by class. The generator of such a class refers to
     * them wherever it is used, also when it is not derived again.
     *
     * @var array<lowercase-string, list<lowercase-string>>
     */
    private array $classesReferredTo = [];

    /**
     * How often the generator of a class that is being derived was referred
     * to from within the generators for the arguments of its constructor, by
     * class. A class is no longer listed, and the references to it are no
     * longer counted, once its generator is derived, so that a nullable value
     * or a collection that contains objects of the class, but that is not
     * used for its constructor, is not considered to be recursive.
     *
     * @var array<lowercase-string, non-negative-int>
     */
    private array $classesBeingDerived = [];

    /**
     * @var WeakMap<DeferredGenerator<mixed>, AbstractGenerator<mixed>>
     */
    private WeakMap $deferred;

    /**
     * The types that the doc comments of methods declare for their
     * parameters, by method, so that the doc comment of a method is parsed
     * once rather than once for each of its parameters.
     *
     * @var array<non-empty-string, ParameterTypes>
     */
    private array $parameterTypes = [];

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
            [$type, $declaration] = $this->typeOf($parameter, $this->parameterTypesOf($method));
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
            $numberOfRecursiveReferences = $this->numberOfRecursiveReferences();
            $generator                   = $this->forType($type->type(), $scope);

            return $this->nullable($generator, $this->numberOfRecursiveReferences() > $numberOfRecursiveReferences);
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
     * for points in time, time zones, or the cases of an enumeration, or one
     * that creates objects of the class with new.
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

        if ($key === 'datetimezone') {
            return $this->timeZones();
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

        if (isset($this->classes[$key], $this->classesReferredTo[$key])) {
            foreach ($this->classesReferredTo[$key] as $classReferredTo) {
                if (isset($this->classesBeingDerived[$classReferredTo])) {
                    $this->classesBeingDerived[$classReferredTo]++;
                }
            }

            return $this->classes[$key];
        }

        if (isset($this->classesBeingDerived[$key])) {
            $this->classesBeingDerived[$key]++;

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

        $classesBeingDerived             = $this->classesBeingDerived;
        $this->classesBeingDerived[$key] = 0;

        try {
            $arguments = [];

            if ($constructor !== null) {
                $arguments = $this->constructorArguments($constructor);
            }
        } finally {
            unset($this->classesBeingDerived[$key]);
        }

        $classesReferredTo = [];

        foreach ($classesBeingDerived as $classBeingDerived => $numberOfReferences) {
            if (isset($this->classesBeingDerived[$classBeingDerived]) && $this->classesBeingDerived[$classBeingDerived] > $numberOfReferences) {
                $classesReferredTo[] = $classBeingDerived;
            }
        }

        $generator = new ObjectGenerator($className, $arguments);

        $this->classes[$key]           = $generator;
        $this->classesReferredTo[$key] = $classesReferredTo;

        return $generator;
    }

    /**
     * Variadic parameters of the constructor receive no arguments.
     *
     * Neither does a parameter that has a default value when no generator can
     * be derived for its type, so that the constructor uses the default value
     * rather than no object of the class being generated at all. Values are
     * generated for a parameter that has a default value when a generator can
     * be derived for its type.
     *
     * A type in a doc comment that cannot be parsed is reported for a
     * parameter that has a default value, too, also when it is the type of a
     * parameter of the constructor of a class that the type of the parameter
     * refers to, as the default value would otherwise hide a type that is not
     * written correctly.
     *
     * @throws CannotDeriveGeneratorException
     *
     * @return array<non-empty-string, AbstractGenerator<mixed>>
     */
    private function constructorArguments(ReflectionMethod $constructor): array
    {
        $scope     = NameScope::forMethod($constructor);
        $docTypes  = $this->parameterTypesOf($constructor);
        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            if ($parameter->isVariadic()) {
                continue;
            }

            try {
                [$type, $declaration] = $this->typeOf($parameter, $docTypes);
            } catch (InvalidTypeException $e) {
                throw CannotDeriveGeneratorException::unparsableConstructorParameterType($parameter, $docTypes->tagOf($parameter->getName()), $e);
            } catch (CannotDeriveGeneratorException $e) {
                if ($parameter->isDefaultValueAvailable()) {
                    continue;
                }

                throw CannotDeriveGeneratorException::forConstructorParameter($parameter, (string) $parameter->getType(), $e);
            }

            $classesBeingDerived = $this->classesBeingDerived;

            try {
                $arguments[$parameter->getName()] = $this->forType($type, $scope);
            } catch (CannotDeriveGeneratorException $e) {
                if ($parameter->isDefaultValueAvailable() && !$e->isCausedByTypeThatCannotBeParsed()) {
                    // the generators that referred to a class that is being
                    // derived before it turned out that no generator can be
                    // derived for the type are not used, so that these
                    // references are not counted
                    $this->classesBeingDerived = $classesBeingDerived;

                    continue;
                }

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

        if ($this->isClassNamedLikeKeyword($type, $scope)) {
            return $this->forClass($scope->resolve($type->name()));
        }

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
     * Whether the name of the type is a keyword that PHP does not reserve
     * that refers to a class, an interface, or a trait of that name rather
     * than to the keyword.
     */
    private function isClassNamedLikeKeyword(NamedType $type, NameScope $scope): bool
    {
        if (!in_array(strtolower($type->name()), self::KEYWORDS_THAT_CAN_BE_CLASS_NAMES, true)) {
            return false;
        }

        return $this->isDeclaredAs($scope->resolve($type->name()));
    }

    /**
     * Whether a class, an interface, or a trait is declared with exactly this
     * name. PHP finds one that is loaded by its name in any case, but an
     * autoloader only finds one by the name with which it is declared: when
     * the case did not matter, a name such as resource in the namespace of a
     * class named Resource would refer to the class when the class happens to
     * be loaded, and to the keyword when it is not.
     */
    private function isDeclaredAs(string $className): bool
    {
        if (!class_exists($className) && !interface_exists($className) && !trait_exists($className)) {
            return false;
        }

        return new ReflectionClass($className)->getName() === $className;
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
            'float', 'double'                   => new FloatGenerator(-PHP_FLOAT_MAX, PHP_FLOAT_MAX, false, false, true),
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
                    new MappedGenerator(new FloatGenerator(-PHP_FLOAT_MAX, PHP_FLOAT_MAX, false, false, true), static fn (float $value): string => (string) $value),
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
        // a name with a hyphen, such as int-mask, cannot be the name of a class
        if (in_array(strtolower($type->name()), self::UNSUPPORTED_KEYWORDS, true) || str_contains($type->name(), '-')) {
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
     * A type for the keys is only supported when all of its values are
     * integers or strings that PHP uses as the keys of an array as they are.
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

        $numberOfRecursiveReferences = $this->numberOfRecursiveReferences();
        $keys                        = $this->arrayKeys();

        if (count($arguments) === 2) {
            $keys = $this->forType($arguments[0], $scope);

            $this->ensureValuesAreArrayKeys($type, $arguments[0], $scope);
        }

        $values = $this->values();

        if ($arguments !== []) {
            $values = $this->forType($arguments[count($arguments) - 1], $scope);
        }

        return new ArrayGenerator($keys, $values, $this->minimumSize($nonEmpty), null, self::probabilityOfAnotherElement($this->numberOfRecursiveReferences() > $numberOfRecursiveReferences));
    }

    /**
     * PHP casts a key that is a boolean, a floating-point number, or null to
     * an integer or a string, uses an integer in place of a string that is
     * the decimal representation of that integer, such as '1', and does not
     * accept any other value, such as a case of an enumeration, as a key. An
     * array whose keys are of the type that is declared for them can
     * therefore only be generated when the values of that type, and of every
     * member of a union, are integers or strings.
     *
     * A type whose values include strings that PHP does not use as keys as
     * they are, such as string and numeric-string, is supported, as the
     * generator for the array does not use such a string as a key.
     *
     * @param NamedType $type the type of the array
     * @param Type      $keys the type of the keys, for which a generator can be derived
     *
     * @throws CannotDeriveGeneratorException
     */
    private function ensureValuesAreArrayKeys(NamedType $type, Type $keys, NameScope $scope): void
    {
        if ($keys instanceof UnionType) {
            foreach ($keys->types() as $member) {
                $this->ensureValuesAreArrayKeys($type, $member, $scope);
            }

            return;
        }

        if ($keys instanceof LiteralType) {
            $value = $keys->value();

            if (is_int($value)) {
                return;
            }

            if (is_string($value)) {
                $key = array_key_first([$value => true]);

                if (is_int($key)) {
                    throw CannotDeriveGeneratorException::invalidTypeArguments($type->asString(), sprintf('PHP uses the integer %d rather than the string %s as the key of an array', $key, $keys->asString()));
                }

                return;
            }
        }

        if ($keys instanceof NamedType && in_array(strtolower($keys->name()), self::KEYWORDS_OF_ARRAY_KEYS, true) && !$this->isClassNamedLikeKeyword($keys, $scope)) {
            return;
        }

        $typeOfKeys = $keys->asString();

        if ($keys instanceof NullableType) {
            $typeOfKeys = 'null';
        }

        throw CannotDeriveGeneratorException::invalidTypeArguments($type->asString(), sprintf('the keys of an array are integers or strings, not %s', $typeOfKeys));
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

        $numberOfRecursiveReferences = $this->numberOfRecursiveReferences();
        $elements                    = $this->values();

        if ($arguments !== []) {
            $elements = $this->forType($arguments[0], $scope);
        }

        return new ListGenerator($elements, $this->minimumSize($nonEmpty), null, self::probabilityOfAnotherElement($this->numberOfRecursiveReferences() > $numberOfRecursiveReferences));
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
        $numberOfRecursiveReferences = $this->numberOfRecursiveReferences();
        $nonRecursive                = [];
        $recursive                   = [];
        $nullable                    = false;

        foreach ($type->types() as $member) {
            if ($member instanceof NamedType && $member->arguments() === [] && strtolower($member->name()) === 'null') {
                $nullable = true;

                continue;
            }

            $numberOfRecursiveReferencesBefore = $this->numberOfRecursiveReferences();
            $generator                         = $this->forType($member, $scope);

            if ($this->numberOfRecursiveReferences() > $numberOfRecursiveReferencesBefore) {
                $recursive[] = $generator;
            } else {
                $nonRecursive[] = $generator;
            }
        }

        if ($nonRecursive === [] && $recursive === []) {
            return new ConstantGenerator(null);
        }

        if ($recursive === []) {
            $generator = $this->oneOf($nonRecursive);
        } elseif ($nonRecursive === []) {
            $generator = $this->oneOf($recursive);
        } else {
            // the members that refer to a class that is being derived are
            // chosen less often than the others, which are the simplest, so
            // that the generated values stay small
            $generator = new OneOfGenerator(
                [$this->oneOf($nonRecursive), $this->oneOf($recursive)],
                new BernoulliDistribution(self::PROBABILITY_OF_RECURSION),
            );
        }

        if ($nullable) {
            return $this->nullable($generator, $this->numberOfRecursiveReferences() > $numberOfRecursiveReferences);
        }

        return $generator;
    }

    /**
     * How often the generators of the classes that are being derived were
     * referred to so far. A generator refers to a class that is being
     * derived, and generates values that can contain objects of that class
     * in objects of that class, when this number is greater after it was
     * derived than before.
     */
    private function numberOfRecursiveReferences(): int
    {
        return array_sum($this->classesBeingDerived);
    }

    private function parameterTypesOf(ReflectionMethod $method): ParameterTypes
    {
        $key = $method->getDeclaringClass()->getName() . '::' . $method->getName();

        if (!isset($this->parameterTypes[$key])) {
            $parameterTypes = ParameterTypes::fromDocComment(DocComments::of($method));

            if ($method->isConstructor()) {
                $parameterTypes = $parameterTypes->withTypesOfPromotedParameters(DocComments::ofPromotedParameters($method));
            }

            $this->parameterTypes[$key] = $parameterTypes;
        }

        return $this->parameterTypes[$key];
    }

    /**
     * @param non-empty-list<AbstractGenerator<mixed>> $generators
     *
     * @return AbstractGenerator<mixed>
     */
    private function oneOf(array $generators): AbstractGenerator
    {
        if (count($generators) === 1) {
            return $generators[0];
        }

        return new OneOfGenerator($generators);
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
                new FloatGenerator(-PHP_FLOAT_MAX, PHP_FLOAT_MAX, false, false, true),
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
     * Generates the time zones whose identifiers Generators::timezones()
     * generates, the simplest of which is UTC, as the constructor of
     * DateTimeZone does not accept other strings.
     *
     * @return AbstractGenerator<DateTimeZone>
     */
    private function timeZones(): AbstractGenerator
    {
        return new ObjectGenerator(DateTimeZone::class, ['timezone' => AbstractGenerator::from(Generators::timezones())]);
    }

    /**
     * Returns the type of the parameter, from its @param tag, from the @var
     * tag of a promoted constructor parameter, or from its native type
     * declaration, together with the type as it is declared.
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
