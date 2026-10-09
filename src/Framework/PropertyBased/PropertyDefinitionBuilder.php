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

use function array_fill_keys;
use function array_keys;
use function assert;
use function class_exists;
use function interface_exists;
use function is_string;
use function lcfirst;
use function method_exists;
use function sprintf;
use function strtolower;
use Closure;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorRegistry;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\ForAll;
use PHPUnit\Metadata\GeneratorFor;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

/**
 * Validates the metadata of a property before it is checked.
 *
 * The generators for the parameters whose generator is not specified with
 * #[ForAll] are derived from their types, so that a parameter for which no
 * generator can be determined is reported before the property is checked.
 * The methods that are registered with #[GeneratorFor] are not called for
 * this: it is enough to know for which classes generators are registered.
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

        if ($metadata->isDataProvider()->isNotEmpty() ||
            $metadata->isDataProviderClosure()->isNotEmpty() ||
            $metadata->isTestWith()->isNotEmpty()) {
            throw InvalidPropertyException::because(
                $className,
                $methodName,
                '#[Property] cannot be combined with #[DataProvider], #[DataProviderClosure], #[DataProviderExternal], #[TestWith], or #[TestWithJson]',
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

        $deriver = new GeneratorDeriver(
            GeneratorRegistry::from(
                array_fill_keys(array_keys($registeredGenerators), Generators::constant(null)),
            ),
        );

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
                $deriver->forParameter($parameter);
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

            $parameters[$name] = null;
        }

        return new PropertyDefinition($className, $methodName, $parameters, $registeredGenerators);
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

        foreach (new ReflectionClass($className)->getMethods() as $method) {
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

                if (!class_exists($registeredClassName) && !interface_exists($registeredClassName)) {
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

                $key = strtolower($registeredClassName);

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
