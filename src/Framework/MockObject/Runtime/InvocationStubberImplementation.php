<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use const PHP_EOL;
use function array_flip;
use function array_key_exists;
use function array_map;
use function array_merge;
use function array_pop;
use function assert;
use function count;
use function is_string;
use function range;
use function strtolower;
use PHPUnit\Event\Facade as EventFacade;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\InvalidArgumentException;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
use PHPUnit\Framework\MockObject\Stub\ConsecutiveCalls;
use PHPUnit\Framework\MockObject\Stub\Exception;
use PHPUnit\Framework\MockObject\Stub\ReturnArgument;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;
use PHPUnit\Framework\MockObject\Stub\ReturnReference;
use PHPUnit\Framework\MockObject\Stub\ReturnSelf;
use PHPUnit\Framework\MockObject\Stub\ReturnStub;
use PHPUnit\Framework\MockObject\Stub\ReturnValueMap;
use PHPUnit\Framework\MockObject\Stub\Stub;
use Throwable;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class InvocationStubberImplementation implements InvocationStubber
{
    private readonly InvocationHandler $invocationHandler;
    private readonly Matcher $matcher;

    /**
     * @var list<ConfigurableMethod>
     */
    private readonly array $configurableMethods;

    /**
     * @var ?array<string, int>
     */
    private ?array $configurableMethodNames = null;

    public function __construct(InvocationHandler $handler, Matcher $matcher, ConfigurableMethod ...$configurableMethods)
    {
        $this->invocationHandler   = $handler;
        $this->matcher             = $matcher;
        $this->configurableMethods = $configurableMethods;
    }

    /**
     * @param Constraint|non-empty-string|PropertyHook $constraint
     *
     * @throws InvalidArgumentException
     * @throws MethodCannotBeConfiguredException
     * @throws MethodNameAlreadyConfiguredException
     *
     * @return $this
     */
    public function method(Constraint|PropertyHook|string $constraint): InvocationStubber
    {
        if ($this->matcher->hasMethodNameRule()) {
            throw new MethodNameAlreadyConfiguredException;
        }

        if ($constraint instanceof PropertyHook) {
            $constraint = $constraint->asString();
        }

        if (is_string($constraint)) {
            $this->configurableMethodNames ??= array_flip(
                array_map(
                    static fn (ConfigurableMethod $configurable) => strtolower($configurable->name()),
                    $this->configurableMethods,
                ),
            );

            if (!array_key_exists(strtolower($constraint), $this->configurableMethodNames)) {
                throw new MethodCannotBeConfiguredException($constraint);
            }
        }

        $this->matcher->setMethodNameRule(new Rule\MethodName($constraint));

        return $this;
    }

    /**
     * @param non-empty-string $id
     *
     * @throws MatcherAlreadyRegisteredException
     *
     * @return $this
     */
    public function id(string $id): InvocationStubber
    {
        $this->invocationHandler->registerMatcher($id, $this->matcher);

        return $this;
    }

    /**
     * @param non-empty-string $id
     *
     * @return $this
     */
    public function after(string $id): InvocationStubber
    {
        $this->matcher->setAfterMatchBuilderId($id);

        return $this;
    }

    /**
     * @throws \PHPUnit\Framework\Exception
     * @throws MethodNameNotConfiguredException
     * @throws MethodParametersAlreadyConfiguredException
     *
     * @return $this
     */
    public function with(mixed ...$arguments): InvocationStubber
    {
        $this->ensureParametersCanBeConfigured();
        $this->emitDeprecationForWithMethods();

        $this->matcher->setParametersRule(new Rule\Parameters($arguments));

        return $this;
    }

    /**
     * @throws MethodNameNotConfiguredException
     * @throws MethodParametersAlreadyConfiguredException
     *
     * @return $this
     */
    public function withAnyParameters(): InvocationStubber
    {
        $this->ensureParametersCanBeConfigured();
        $this->emitDeprecationForWithMethods();

        $this->matcher->setParametersRule(new Rule\AnyParameters);

        return $this;
    }

    /**
     * @return $this
     */
    public function will(Stub $stub): InvocationStubber
    {
        $this->matcher->setStub($stub);

        return $this;
    }

    /**
     * @throws IncompatibleReturnValueException
     */
    public function willReturn(mixed $value, mixed ...$nextValues): InvocationStubber
    {
        if (count($nextValues) === 0) {
            $this->ensureTypeOfReturnValues([$value]);

            $stub = $value instanceof Stub ? $value : new ReturnStub($value);

            return $this->will($stub);
        }

        $values = array_merge([$value], $nextValues);

        $this->ensureTypeOfReturnValues($values);

        $stub = new ConsecutiveCalls($values);

        return $this->will($stub);
    }

    public function willReturnReference(mixed &$reference): InvocationStubber
    {
        $stub = new ReturnReference($reference);

        return $this->will($stub);
    }

    public function willReturnMap(array $valueMap): InvocationStubber
    {
        $method = $this->configuredMethod();

        assert($method instanceof ConfigurableMethod);

        $numberOfParameters = $method->numberOfParameters();
        $defaultValues      = $method->defaultParameterValues();
        $hasDefaultValues   = $defaultValues !== [];

        $_valueMap = [];

        foreach ($valueMap as $mapping) {
            $numberOfConfiguredParameters = count($mapping) - 1;

            if ($numberOfConfiguredParameters === $numberOfParameters || !$hasDefaultValues) {
                $_valueMap[] = $mapping;

                continue;
            }

            $_mapping    = [];
            $returnValue = array_pop($mapping);

            foreach (range(0, $numberOfParameters - 1) as $i) {
                if (array_key_exists($i, $mapping)) {
                    $_mapping[] = $mapping[$i];

                    continue;
                }

                if (array_key_exists($i, $defaultValues)) {
                    $_mapping[] = $defaultValues[$i];
                }
            }

            $_mapping[]  = $returnValue;
            $_valueMap[] = $_mapping;
        }

        $stub = new ReturnValueMap($_valueMap);

        return $this->will($stub);
    }

    public function willReturnArgument(int $argumentIndex): InvocationStubber
    {
        $stub = new ReturnArgument($argumentIndex);

        return $this->will($stub);
    }

    public function willReturnCallback(callable $callback): InvocationStubber
    {
        $stub = new ReturnCallback($callback);

        return $this->will($stub);
    }

    public function willReturnSelf(): InvocationStubber
    {
        $stub = new ReturnSelf;

        return $this->will($stub);
    }

    public function willReturnOnConsecutiveCalls(mixed ...$values): InvocationStubber
    {
        $stub = new ConsecutiveCalls($values);

        return $this->will($stub);
    }

    public function willThrowException(Throwable $exception): InvocationStubber
    {
        $stub = new Exception($exception);

        return $this->will($stub);
    }

    /**
     * @throws MethodNameNotConfiguredException
     * @throws MethodParametersAlreadyConfiguredException
     */
    private function ensureParametersCanBeConfigured(): void
    {
        if (!$this->matcher->hasMethodNameRule()) {
            throw new MethodNameNotConfiguredException;
        }

        if ($this->matcher->hasParametersRule()) {
            throw new MethodParametersAlreadyConfiguredException;
        }
    }

    private function configuredMethod(): ?ConfigurableMethod
    {
        $configuredMethod = null;

        foreach ($this->configurableMethods as $configurableMethod) {
            if ($this->matcher->methodNameRule()->matchesName($configurableMethod->name())) {
                if ($configuredMethod !== null) {
                    return null;
                }

                $configuredMethod = $configurableMethod;
            }
        }

        return $configuredMethod;
    }

    /**
     * @param array<mixed> $values
     *
     * @throws IncompatibleReturnValueException
     */
    private function ensureTypeOfReturnValues(array $values): void
    {
        $configuredMethod = $this->configuredMethod();

        if ($configuredMethod === null) {
            return;
        }

        foreach ($values as $value) {
            if (!$configuredMethod->mayReturn($value)) {
                throw new IncompatibleReturnValueException(
                    $configuredMethod,
                    $value,
                );
            }
        }
    }

    private function emitDeprecationForWithMethods(): void
    {
        if ($this->invocationHandler->isMockObject()) {
            return;
        }

        EventFacade::emitter()->testTriggeredPhpunitDeprecation(
            null,
            'Using with*() on a test stub has no effect and is deprecated.' . PHP_EOL .
            'With PHPUnit 13, it will not be possible to use with() on a test stub.',
        );
    }
}
