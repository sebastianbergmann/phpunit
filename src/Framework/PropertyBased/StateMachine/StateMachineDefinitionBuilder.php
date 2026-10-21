<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\PropertyBased\StateMachine;

use function assert;
use function count;
use function get_debug_type;
use function in_array;
use function lcfirst;
use function method_exists;
use function preg_match;
use function sprintf;
use Closure;
use PHPUnit\Framework\PropertyBased\CannotDeriveGeneratorException;
use PHPUnit\Framework\PropertyBased\Derivation\GeneratorDeriver;
use PHPUnit\Framework\PropertyBased\Generator\AbstractGenerator;
use PHPUnit\Framework\PropertyBased\GeneratorMethodValidator;
use PHPUnit\Framework\PropertyBased\InvalidGeneratorMethodException;
use PHPUnit\Framework\PropertyBased\InvalidStateMachineException;
use PHPUnit\Framework\PropertyBased\ValueGenerator;
use PHPUnit\Framework\PropertyBased\ValueGeneratorNotSupportedException;
use PHPUnit\Metadata\ForAll;
use PHPUnit\Metadata\FromPool;
use PHPUnit\Metadata\Initialize;
use PHPUnit\Metadata\Invariant;
use PHPUnit\Metadata\MetadataCollection;
use PHPUnit\Metadata\Parser\Registry as MetadataRegistry;
use PHPUnit\Metadata\Rule;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Throwable;

/**
 * Validates the metadata of a state machine before it is run, and creates the
 * generators for the parameters of its steps that do not receive values from
 * pools: the generators that are specified with #[ForAll] or #[ForAllExternal]
 * and the generators that are derived from the types of the parameters with
 * the deriver of the property that runs the state machine, which knows the
 * generators that are registered for it with #[GeneratorFor].
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final readonly class StateMachineDefinitionBuilder
{
    /**
     * The names of pools become the names of variables when the steps of a
     * counterexample are reported.
     */
    private const string POOL_NAME = '/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/';

    /**
     * @param class-string $className
     *
     * @throws InvalidStateMachineException
     */
    public function build(string $className, GeneratorDeriver $deriver): StateMachineDefinition
    {
        $initializations = [];
        $rules           = [];
        $invariants      = [];
        $invariantsAtEnd = [];

        foreach (new ReflectionClass($className)->getMethods() as $method) {
            $methodName = $method->getName();

            assert($methodName !== '');

            $metadata   = MetadataRegistry::parser()->forMethod($method->getDeclaringClass()->getName(), $methodName);
            $rule       = $metadata->isRule();
            $initialize = $metadata->isInitialize();
            $invariant  = $metadata->isInvariant();

            if (count($rule) + count($initialize) + count($invariant) === 0) {
                continue;
            }

            if (count($rule) + count($initialize) + count($invariant) > 1) {
                throw InvalidStateMachineException::because(
                    $className,
                    sprintf('method %s() has more than one of #[Rule], #[Initialize], and #[Invariant]', $methodName),
                );
            }

            if ($invariant->isNotEmpty()) {
                $item = $invariant->asArray()[0];

                assert($item instanceof Invariant);

                $this->ensureCanBeCalled($className, $method, sprintf('invariant %s()', $methodName));

                if ($method->getNumberOfRequiredParameters() > 0) {
                    throw InvalidStateMachineException::because(
                        $className,
                        sprintf('invariant %s() has required parameters', $methodName),
                    );
                }

                if ($item->atEnd()) {
                    $invariantsAtEnd[] = $methodName;
                } else {
                    $invariants[] = $methodName;
                }

                continue;
            }

            if ($rule->isNotEmpty()) {
                $item = $rule->asArray()[0];

                assert($item instanceof Rule);

                $description  = sprintf('rule %s()', $methodName);
                $precondition = $item->preconditionMethod();
                $produces     = $item->produces();

                if ($precondition !== null) {
                    $precondition = $this->precondition($className, $description, $precondition);
                }

                $rules[] = new StepDefinition(
                    $methodName,
                    $precondition,
                    $this->produces($className, $method, $description, $produces),
                    $this->parameters($className, $method, $description, $metadata, $deriver, true),
                );

                continue;
            }

            if ($initialize->isNotEmpty()) {
                $item = $initialize->asArray()[0];

                assert($item instanceof Initialize);

                $description = sprintf('initialization %s()', $methodName);

                $initializations[] = new StepDefinition(
                    $methodName,
                    null,
                    $this->produces($className, $method, $description, $item->produces()),
                    $this->parameters($className, $method, $description, $metadata, $deriver, false),
                );
            }
        }

        if ($rules === []) {
            throw InvalidStateMachineException::because($className, 'it has no method with #[Rule]');
        }

        $this->ensurePoolsAreProduced($className, $initializations, $rules);

        return new StateMachineDefinition($className, $initializations, $rules, $invariants, $invariantsAtEnd);
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $description
     *
     * @throws InvalidStateMachineException
     */
    private function ensureCanBeCalled(string $className, ReflectionMethod $method, string $description): void
    {
        if (!$method->isPublic()) {
            throw InvalidStateMachineException::because(
                $className,
                sprintf('%s is not public', $description),
            );
        }

        if ($method->isStatic()) {
            throw InvalidStateMachineException::because(
                $className,
                sprintf('%s is static', $description),
            );
        }
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $description
     *
     * @throws InvalidStateMachineException
     *
     * @return non-empty-string
     */
    private function precondition(string $className, string $description, string $precondition): string
    {
        if ($precondition === '') {
            throw InvalidStateMachineException::because(
                $className,
                sprintf('the precondition of %s is specified as a method whose name is empty', $description),
            );
        }

        if (!method_exists($className, $precondition)) {
            throw InvalidStateMachineException::because(
                $className,
                sprintf('the precondition of %s is specified as %s(), which does not exist', $description, $precondition),
            );
        }

        $method = new ReflectionMethod($className, $precondition);

        $this->ensureCanBeCalled($className, $method, sprintf('the precondition %s() of %s', $precondition, $description));

        if ($method->getNumberOfRequiredParameters() > 0) {
            throw InvalidStateMachineException::because(
                $className,
                sprintf('the precondition %s() of %s has required parameters', $precondition, $description),
            );
        }

        return $precondition;
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $description
     *
     * @throws InvalidStateMachineException
     *
     * @return ?non-empty-string
     */
    private function produces(string $className, ReflectionMethod $method, string $description, ?string $pool): ?string
    {
        $this->ensureCanBeCalled($className, $method, $description);

        if ($pool === null) {
            return null;
        }

        if ($pool === '') {
            throw InvalidStateMachineException::because(
                $className,
                sprintf('%s produces values for a pool whose name is empty', $description),
            );
        }

        if (preg_match(self::POOL_NAME, $pool) !== 1) {
            throw InvalidStateMachineException::because(
                $className,
                sprintf('%s produces values for pool "%s", whose name is not a valid variable name', $description, $pool),
            );
        }

        $returnType = $method->getReturnType();

        if ($returnType instanceof ReflectionNamedType && in_array($returnType->getName(), ['void', 'never'], true)) {
            throw InvalidStateMachineException::because(
                $className,
                sprintf('%s produces values for pool "%s", but its return type is %s', $description, $pool, $returnType->getName()),
            );
        }

        return $pool;
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $description
     *
     * @throws InvalidStateMachineException
     *
     * @return list<ParameterDefinition>
     */
    private function parameters(string $className, ReflectionMethod $method, string $description, MetadataCollection $metadata, GeneratorDeriver $deriver, bool $isRule): array
    {
        $forAll   = [];
        $fromPool = [];

        foreach ($metadata->isForAll() as $item) {
            assert($item instanceof ForAll);

            $parameterName = $item->parameterName();

            // #[ForAll] and #[ForAllExternal] cannot be repeated, but they can
            // be combined
            if (isset($forAll[$parameterName])) {
                throw InvalidStateMachineException::because(
                    $className,
                    sprintf('the generator for parameter $%s of %s is specified more than once', $parameterName, $description),
                );
            }

            $forAll[$parameterName] = $item;
        }

        foreach ($metadata->isFromPool() as $item) {
            assert($item instanceof FromPool);

            $fromPool[$item->parameterName()] = $item;
        }

        $parameters = [];

        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->getName();

            assert($name !== '');

            if ($parameter->isVariadic()) {
                throw InvalidStateMachineException::because(
                    $className,
                    sprintf('variadic parameter $%s of %s is not supported', $name, $description),
                );
            }

            if (isset($fromPool[$name])) {
                $parameters[] = $this->parameterFromPool($className, $description, $name, $fromPool[$name], isset($forAll[$name]), $isRule);

                continue;
            }

            if (isset($forAll[$name])) {
                $parameters[] = ParameterDefinition::generated(
                    $name,
                    $this->generatorSpecifiedWithForAll($className, $description, $forAll[$name]),
                );

                continue;
            }

            try {
                $parameters[] = ParameterDefinition::generated($name, $deriver->forParameter($parameter));
            } catch (CannotDeriveGeneratorException $e) {
                throw InvalidStateMachineException::because(
                    $className,
                    sprintf('%s: %s', $description, lcfirst($e->getMessage())),
                );
            } catch (Throwable $t) {
                // for instance, the class of the parameter cannot be loaded
                // because its parent class does not exist
                throw InvalidStateMachineException::because(
                    $className,
                    sprintf(
                        '%s: cannot derive a generator for parameter $%s, %s was raised: %s',
                        $description,
                        $name,
                        $t::class,
                        $t->getMessage(),
                    ),
                );
            }
        }

        return $parameters;
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $description
     * @param non-empty-string $name
     *
     * @throws InvalidStateMachineException
     */
    private function parameterFromPool(string $className, string $description, string $name, FromPool $fromPool, bool $hasForAll, bool $isRule): ParameterDefinition
    {
        if (!$isRule) {
            throw InvalidStateMachineException::because(
                $className,
                sprintf('parameter $%s of %s cannot take values from a pool, only the parameters of rules can', $name, $description),
            );
        }

        if ($hasForAll) {
            throw InvalidStateMachineException::because(
                $className,
                sprintf('parameter $%s of %s takes values from a pool, so its generator cannot be specified', $name, $description),
            );
        }

        $pool = $fromPool->pool();

        if ($pool === '') {
            throw InvalidStateMachineException::because(
                $className,
                sprintf('parameter $%s of %s takes values from a pool whose name is empty', $name, $description),
            );
        }

        return ParameterDefinition::fromPool($name, $pool, $fromPool->consume());
    }

    /**
     * @param class-string     $className
     * @param non-empty-string $description
     *
     * @throws InvalidStateMachineException
     *
     * @return AbstractGenerator<mixed>
     */
    private function generatorSpecifiedWithForAll(string $className, string $description, ForAll $forAll): AbstractGenerator
    {
        try {
            $generator = new GeneratorMethodValidator()->specifiedWithForAll($forAll);
        } catch (InvalidGeneratorMethodException $e) {
            throw InvalidStateMachineException::because(
                $className,
                sprintf('%s: %s', $description, $e->getMessage()),
            );
        }

        if ($generator instanceof Closure) {
            $source = sprintf('the closure that is specified with #[ForAll] for parameter $%s of %s', $forAll->parameterName(), $description);
            $value  = $generator();
        } else {
            $source = sprintf('%s, which is specified for parameter $%s of %s,', $generator->asString(), $forAll->parameterName(), $description);
            $value  = new ReflectionMethod($generator->className(), $generator->methodName())->invoke(null);
        }

        if (!$value instanceof ValueGenerator) {
            throw InvalidStateMachineException::because(
                $className,
                sprintf('%s does not return a %s, but %s', $source, ValueGenerator::class, get_debug_type($value)),
            );
        }

        try {
            return AbstractGenerator::from($value);
        } catch (ValueGeneratorNotSupportedException $e) {
            throw InvalidStateMachineException::because(
                $className,
                sprintf('%s: %s', $description, lcfirst($e->getMessage())),
            );
        }
    }

    /**
     * @param class-string                   $className
     * @param list<StepDefinition>           $initializations
     * @param non-empty-list<StepDefinition> $rules
     *
     * @throws InvalidStateMachineException
     */
    private function ensurePoolsAreProduced(string $className, array $initializations, array $rules): void
    {
        $produced = [];

        foreach ([...$initializations, ...$rules] as $step) {
            if ($step->produces() !== null) {
                $produced[$step->produces()] = true;
            }
        }

        foreach ($rules as $rule) {
            foreach ($rule->parameters() as $parameter) {
                $pool = $parameter->pool();

                if ($pool === null || isset($produced[$pool])) {
                    continue;
                }

                throw InvalidStateMachineException::because(
                    $className,
                    sprintf(
                        'parameter $%s of rule %s() takes values from pool "%s", for which no rule or initialization produces values',
                        $parameter->name(),
                        $rule->methodName(),
                        $pool,
                    ),
                );
            }
        }
    }
}
