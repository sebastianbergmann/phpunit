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

use function array_key_exists;
use function array_keys;
use function assert;
use function class_exists;
use function get_debug_type;
use function in_array;
use function interface_exists;
use function is_array;
use function is_int;
use function is_string;
use function lcfirst;
use function method_exists;
use function sprintf;
use Closure;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorRegistry;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\Generator\DeferredGenerator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\ForAll;
use PHPUnit\Metadata\GeneratorFor;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use PHPUnit\Metadata\TestWith;
use PHPUnit\Util\Reflection;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

/**
 * Validates the metadata of a property before it is checked.
 *
 * The generators for the parameters whose generator is not specified with
 * #[ForAll] are derived from their types, so that a parameter for which no
 * generator can be determined is reported before the property is checked,
 * and they are kept for when it is checked. The methods that are registered
 * with #[GeneratorFor] are not called for this: a generator that is derived
 * refers to a class for which a generator is registered with a deferred
 * generator, which uses the registered generator once it has been created
 * when the property is checked.
 *
 * The arguments of an explicit example, which is specified with #[TestWith]
 * or #[TestWithJson], are passed to the parameters by position or by name,
 * like the arguments of a data set are passed to a test method, and there
 * has to be exactly one for each parameter.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class PropertyDefinitionBuilder
{
    /**
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     *
     * @throws InvalidPropertyException
     */
    public function build(string $className, string $methodName): PropertyDefinition
    {
        $metadata = MetadataRegistry::parser()->forMethod($className, $methodName);

        assert($metadata->isProperty()->isNotEmpty());

        // the explicit examples of a property are specified with #[TestWith]
        // and #[TestWithJson]
        if ($metadata->isDataProvider()->isNotEmpty() ||
            $metadata->isDataProviderClosure()->isNotEmpty()) {
            throw InvalidPropertyException::because(
                $className,
                $methodName,
                '#[Property] cannot be combined with #[DataProvider], #[DataProviderClosure], or #[DataProviderExternal]',
            );
        }

        // the parameters of a property receive generated values, not the
        // return values of the tests that it depends on
        if ($metadata->isDepends()->isNotEmpty()) {
            throw InvalidPropertyException::because(
                $className,
                $methodName,
                '#[Property] cannot be combined with #[Depends] or any of its variants',
            );
        }

        $forAll = [];

        foreach ($metadata->isForAll() as $item) {
            assert($item instanceof ForAll);

            $forAll[$item->parameterName()] = $item->generator();
        }

        $registeredGenerators = $this->registeredGenerators($className, $methodName);

        $deriver = new GeneratorDeriver($this->deferredRegisteredGenerators($registeredGenerators));

        $parameters = [];

        foreach (new ReflectionMethod($className, $methodName)->getParameters() as $parameter) {
            $name = $parameter->getName();

            assert($name !== '');

            if ($parameter->isVariadic()) {
                throw InvalidPropertyException::because(
                    $className,
                    $methodName,
                    sprintf('variadic parameter $%s is not supported', $name),
                );
            }

            if (isset($forAll[$name])) {
                $this->ensureGeneratorMethodExists($className, $methodName, $name, $forAll[$name]);

                $parameters[$name] = $forAll[$name];

                continue;
            }

            try {
                $parameters[$name] = $deriver->forParameter($parameter);
            } catch (CannotDeriveGeneratorException $e) {
                throw InvalidPropertyException::because($className, $methodName, lcfirst($e->getMessage()));
            } catch (Throwable $t) {
                // for instance, the class of the parameter cannot be loaded
                // because its parent class does not exist
                throw InvalidPropertyException::because(
                    $className,
                    $methodName,
                    sprintf(
                        'cannot derive a generator for parameter $%s, %s was raised: %s',
                        $name,
                        $t::class,
                        $t->getMessage(),
                    ),
                );
            }
        }

        return new PropertyDefinition(
            $className,
            $methodName,
            $parameters,
            $this->explicitExamples($className, $methodName, $metadata->isTestWith(), array_keys($parameters)),
            $registeredGenerators,
        );
    }

    /**
     * The explicit examples are numbered like the data sets of a data
     * provider: an example for which no name is specified is numbered among
     * the examples for which no name is specified.
     *
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     * @param list<non-empty-string> $parameterNames
     *
     * @throws InvalidPropertyException
     *
     * @return list<ExplicitExample>
     */
    private function explicitExamples(string $className, string $methodName, MetadataCollection $testWith, array $parameterNames): array
    {
        $examples = [];
        $labels   = [];
        $number   = 0;

        foreach ($testWith as $metadata) {
            assert($metadata instanceof TestWith);

            if ($metadata->hasName()) {
                $name = $metadata->name();

                assert($name !== null);
            } else {
                $name = $number;

                $number++;
            }

            $label = ExplicitExample::labelFor($name);

            if (isset($labels[$label])) {
                throw InvalidPropertyException::because(
                    $className,
                    $methodName,
                    sprintf('explicit example %s is specified more than once', $label),
                );
            }

            $labels[$label] = true;

            $examples[] = new ExplicitExample(
                $name,
                $this->argumentsOfExplicitExample($className, $methodName, $label, $metadata->data(), $parameterNames),
            );
        }

        return $examples;
    }

    /**
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     * @param non-empty-string       $label
     * @param list<non-empty-string> $parameterNames
     *
     * @throws InvalidPropertyException
     *
     * @return array<non-empty-string, mixed>
     */
    private function argumentsOfExplicitExample(string $className, string $methodName, string $label, mixed $data, array $parameterNames): array
    {
        if (!is_array($data)) {
            throw InvalidPropertyException::because(
                $className,
                $methodName,
                sprintf('explicit example %s is not an array, but %s', $label, get_debug_type($data)),
            );
        }

        $arguments = [];
        $position  = 0;
        $named     = false;

        foreach ($data as $key => $value) {
            if (is_int($key)) {
                if ($named) {
                    throw InvalidPropertyException::because(
                        $className,
                        $methodName,
                        sprintf('explicit example %s has a positional argument after a named argument', $label),
                    );
                }

                if (!isset($parameterNames[$position])) {
                    throw InvalidPropertyException::because(
                        $className,
                        $methodName,
                        sprintf('explicit example %s has more arguments than the property has parameters', $label),
                    );
                }

                $arguments[$parameterNames[$position]] = $value;

                $position++;

                continue;
            }

            $named = true;

            if (!in_array($key, $parameterNames, true)) {
                throw InvalidPropertyException::because(
                    $className,
                    $methodName,
                    sprintf('explicit example %s has an argument for parameter $%s, which does not exist', $label, $key),
                );
            }

            if (array_key_exists($key, $arguments)) {
                throw InvalidPropertyException::because(
                    $className,
                    $methodName,
                    sprintf('explicit example %s has more than one argument for parameter $%s', $label, $key),
                );
            }

            $arguments[$key] = $value;
        }

        $argumentsInOrderOfParameters = [];

        foreach ($parameterNames as $parameterName) {
            if (!array_key_exists($parameterName, $arguments)) {
                throw InvalidPropertyException::because(
                    $className,
                    $methodName,
                    sprintf('explicit example %s has no argument for parameter $%s', $label, $parameterName),
                );
            }

            $argumentsInOrderOfParameters[$parameterName] = $arguments[$parameterName];
        }

        return $argumentsInOrderOfParameters;
    }

    /**
     * Returns the registry of the deferred generators that refer to the
     * generators that are registered for classes, which are derived by the
     * deriver of the choice source as the generators that it registers.
     *
     * @param array<class-string, non-empty-string> $registeredGenerators
     */
    private function deferredRegisteredGenerators(array $registeredGenerators): GeneratorRegistry
    {
        $deferred = [];

        foreach (array_keys($registeredGenerators) as $className) {
            $deferred[$className] = new DeferredGenerator(
                static fn (GeneratorDeriver $deriver): AbstractGenerator => $deriver->forClass($className),
                sprintf('objects of class %s', $className),
            );
        }

        return GeneratorRegistry::from($deferred);
    }

    /**
     * @param class-string<TestCase>                            $className
     * @param non-empty-string                                  $methodName
     * @param non-empty-string                                  $parameterName
     * @param Closure(): ValueGenerator<mixed>|non-empty-string $generator
     *
     * @throws InvalidPropertyException
     */
    private function ensureGeneratorMethodExists(string $className, string $methodName, string $parameterName, Closure|string $generator): void
    {
        if (!is_string($generator)) {
            return;
        }

        if (!method_exists($className, $generator)) {
            throw InvalidPropertyException::because(
                $className,
                $methodName,
                sprintf(
                    'the generator for parameter $%s is specified as %s::%s(), which does not exist',
                    $parameterName,
                    $className,
                    $generator,
                ),
            );
        }

        $method = new ReflectionMethod($className, $generator);

        if (!$method->isPublic() || !$method->isStatic()) {
            throw InvalidPropertyException::because(
                $className,
                $methodName,
                sprintf(
                    'the generator for parameter $%s is specified as %s::%s(), which is not public and static',
                    $parameterName,
                    $className,
                    $generator,
                ),
            );
        }
    }

    /**
     * Returns the methods of the test case class, including inherited methods
     * and methods that are declared in traits, that are registered with
     * #[GeneratorFor], by the class they are registered for.
     *
     * @param class-string<TestCase> $className
     * @param non-empty-string       $methodName
     *
     * @throws InvalidPropertyException
     *
     * @return array<class-string, non-empty-string>
     */
    private function registeredGenerators(string $className, string $methodName): array
    {
        $registeredGenerators = [];
        $registeredFor        = [];

        // the methods that TestCase and Assert declare are not registered
        // with #[GeneratorFor], so their metadata is not looked at
        foreach (Reflection::methodsDeclaredDirectlyInTestClass(new ReflectionClass($className)) as $method) {
            $name = $method->getName();

            assert($name !== '');

            foreach (MetadataRegistry::parser()->forMethod($className, $name)->isGeneratorFor() as $metadata) {
                assert($metadata instanceof GeneratorFor);

                $registeredClassName = $metadata->className();

                if (!$method->isPublic() || !$method->isStatic()) {
                    throw InvalidPropertyException::because(
                        $className,
                        $methodName,
                        sprintf(
                            '%s::%s() is registered with #[GeneratorFor] for %s, but it is not public and static',
                            $className,
                            $name,
                            $registeredClassName,
                        ),
                    );
                }

                try {
                    $exists = class_exists($registeredClassName) || interface_exists($registeredClassName);
                } catch (Throwable $t) {
                    // for instance, the class cannot be loaded because its
                    // parent class does not exist
                    throw InvalidPropertyException::because(
                        $className,
                        $methodName,
                        sprintf(
                            '%s::%s() is registered with #[GeneratorFor] for %s, which cannot be loaded, %s was raised: %s',
                            $className,
                            $name,
                            $registeredClassName,
                            $t::class,
                            $t->getMessage(),
                        ),
                    );
                }

                if (!$exists) {
                    throw InvalidPropertyException::because(
                        $className,
                        $methodName,
                        sprintf(
                            '%s::%s() is registered with #[GeneratorFor] for %s, which is neither a class nor an interface',
                            $className,
                            $name,
                            $registeredClassName,
                        ),
                    );
                }

                $key = GeneratorRegistry::key($registeredClassName);

                if (isset($registeredFor[$key])) {
                    throw InvalidPropertyException::because(
                        $className,
                        $methodName,
                        sprintf(
                            '%s::%s() and %s::%s() are both registered with #[GeneratorFor] for %s',
                            $className,
                            $registeredFor[$key],
                            $className,
                            $name,
                            $registeredClassName,
                        ),
                    );
                }

                $registeredFor[$key]                        = $name;
                $registeredGenerators[$registeredClassName] = $name;
            }
        }

        return $registeredGenerators;
    }
}
