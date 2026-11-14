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

use function assert;
use function sprintf;
use Closure;
use PHPUnit\Event;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorRegistry;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\TestCase;
use PHPUnit\Metadata\Api\Requirements;
use ReflectionMethod;

/**
 * Resolves the generators of the parameters of a property from its
 * definition: the generators that were derived from the types of the
 * parameters, the generator methods and closures that are specified with
 * #[ForAll], and the generators that are registered for classes.
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class GeneratorResolver
{
    private ?PropertyDefinition $definition;

    /**
     * @param ?PropertyDefinition $definition null when the requirements of the property were not met while the test suite was built
     */
    public function __construct(?PropertyDefinition $definition)
    {
        $this->definition = $definition;
    }

    /**
     * The definition of the property, null as long as its requirements are
     * not met.
     */
    public function definition(): ?PropertyDefinition
    {
        return $this->definition;
    }

    /**
     * @throws InvalidPropertyException
     *
     * @return array{0: GeneratorDeriver, 1: array<non-empty-string, AbstractGenerator<mixed>>}
     */
    public function generators(TestCase $test, Event\Emitter $emitter): array
    {
        // the definition of a property whose requirements were not met while
        // the test suite was built is built when the property runs and its
        // requirements are met then, because the function that it requires
        // is declared by setUpBeforeClass(), for instance
        if ($this->definition === null &&
            new Requirements($emitter)->requirementsNotSatisfiedFor($test::class, $test->name()) === []) {
            $this->definition = new PropertyDefinitionBuilder()->build($test::class, $test->name());
        }

        // the examples of a property whose requirements are not met are
        // skipped before arguments are passed to them
        if ($this->definition === null) {
            return [new GeneratorDeriver(GeneratorRegistry::empty()), []];
        }

        $registered = [];

        foreach ($this->definition->registeredGenerators() as $registeredClassName => $generatorMethod) {
            $registered[$registeredClassName] = $this->callGeneratorMethod($generatorMethod);
        }

        // the generators that were derived from the types of the parameters
        // when the definition was built use the registered generators through
        // the deriver of the choice source
        $deriver    = new GeneratorDeriver(GeneratorRegistry::from($registered));
        $generators = [];

        foreach ($this->definition->parameters() as $parameterName => $generator) {
            if ($generator instanceof AbstractGenerator) {
                $generators[$parameterName] = $generator;

                continue;
            }

            if ($generator instanceof Closure) {
                $generators[$parameterName] = AbstractGenerator::from(
                    $this->ensureIsValueGenerator(
                        $generator(),
                        sprintf('the closure that is specified with #[ForAll] for parameter $%s', $parameterName),
                    ),
                );

                continue;
            }

            $generators[$parameterName] = AbstractGenerator::from($this->callGeneratorMethod($generator));
        }

        return [$deriver, $generators];
    }

    /**
     * @throws InvalidPropertyException
     *
     * @return ValueGenerator<mixed>
     */
    private function callGeneratorMethod(GeneratorMethod $generatorMethod): ValueGenerator
    {
        return $this->ensureIsValueGenerator(
            new ReflectionMethod($generatorMethod->className(), $generatorMethod->methodName())->invoke(null),
            $generatorMethod->asString(),
        );
    }

    /**
     * @param non-empty-string $source what returned $generator, for the message of the exception
     *
     * @throws InvalidPropertyException
     *
     * @return ValueGenerator<mixed>
     */
    private function ensureIsValueGenerator(mixed $generator, string $source): ValueGenerator
    {
        assert($this->definition !== null);

        if (!$generator instanceof ValueGenerator) {
            throw InvalidPropertyException::because(
                $this->definition->className(),
                $this->definition->methodName(),
                sprintf(
                    '%s does not return a %s',
                    $source,
                    ValueGenerator::class,
                ),
            );
        }

        return $generator;
    }
}
