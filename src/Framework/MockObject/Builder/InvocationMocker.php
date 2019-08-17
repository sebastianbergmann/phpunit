<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Builder;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\MockObject\ConfigurableMethod;
use PHPUnit\Framework\MockObject\IncompatibleReturnValueException;
use PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\MockObject\Matcher\Invocation;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\MockObject\Stub\ConsecutiveCalls;
use PHPUnit\Framework\MockObject\Stub\Exception;
use PHPUnit\Framework\MockObject\Stub\MatcherCollection;
use PHPUnit\Framework\MockObject\Stub\ReturnArgument;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;
use PHPUnit\Framework\MockObject\Stub\ReturnReference;
use PHPUnit\Framework\MockObject\Stub\ReturnSelf;
use PHPUnit\Framework\MockObject\Stub\ReturnStub;
use PHPUnit\Framework\MockObject\Stub\ReturnValueMap;
use PHPUnit\Framework\MockObject\Stub\Stub;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class InvocationMocker implements MethodNameMatch
{
    /**
     * @var MatcherCollection
     */
    private $collection;

    /**
     * @var Matcher
     */
    private $matcher;

    /**
     * @var ConfigurableMethod[]
     */
    private $configurableMethods;

    public function __construct(MatcherCollection $collection, Invocation $invocationMatcher, ConfigurableMethod ...$configurableMethods)
    {
        $this->collection = $collection;
        $this->matcher    = new Matcher($invocationMatcher);

        $this->collection->addMatcher($this->matcher);

        $this->configurableMethods = $configurableMethods;
    }

    public function getMatcher(): Matcher
    {
        return $this->matcher;
    }

    public function id($id): self
    {
        $this->collection->registerId($id, $this);

        return $this;
    }

    public function will(Stub $stub): Identity
    {
        $this->matcher->setStub($stub);

        return $this;
    }

    public function willReturn($value, ...$nextValues): self
    {
        if (\count($nextValues) === 0) {
            $this->ensureTypeOfReturnValues([$value]);

            $stub = new ReturnStub($value);
        } else {
            $values = \array_merge([$value], $nextValues);

            $this->ensureTypeOfReturnValues($values);

            $stub = new ConsecutiveCalls($values);
        }

        return $this->will($stub);
    }

    /**
     * @param mixed $reference
     */
    public function willReturnReference(&$reference): self
    {
        $stub = new ReturnReference($reference);

        return $this->will($stub);
    }

    public function willReturnMap(array $valueMap): self
    {
        $stub = new ReturnValueMap($valueMap);

        return $this->will($stub);
    }

    public function willReturnArgument($argumentIndex): self
    {
        $stub = new ReturnArgument($argumentIndex);

        return $this->will($stub);
    }

    /**
     * @param callable $callback
     */
    public function willReturnCallback($callback): self
    {
        $stub = new ReturnCallback($callback);

        return $this->will($stub);
    }

    public function willReturnSelf(): self
    {
        $stub = new ReturnSelf;

        return $this->will($stub);
    }

    public function willReturnOnConsecutiveCalls(...$values): self
    {
        $stub = new ConsecutiveCalls($values);

        return $this->will($stub);
    }

    public function willThrowException(\Throwable $exception): self
    {
        $stub = new Exception($exception);

        return $this->will($stub);
    }

    public function after($id): self
    {
        $this->matcher->setAfterMatchBuilderId($id);

        return $this;
    }

    /**
     * @throws RuntimeException
     */
    public function with(...$arguments): self
    {
        $this->canDefineParameters();

        $this->matcher->setParametersMatcher(new Matcher\Parameters($arguments));

        return $this;
    }

    /**
     * @param array ...$arguments
     *
     * @throws RuntimeException
     */
    public function withConsecutive(...$arguments): self
    {
        $this->canDefineParameters();

        $this->matcher->setParametersMatcher(new Matcher\ConsecutiveParameters($arguments));

        return $this;
    }

    /**
     * @throws RuntimeException
     */
    public function withAnyParameters(): self
    {
        $this->canDefineParameters();

        $this->matcher->setParametersMatcher(new Matcher\AnyParameters);

        return $this;
    }

    /**
     * @param Constraint|string $constraint
     *
     * @throws RuntimeException
     */
    public function method($constraint): self
    {
        if ($this->matcher->hasMethodNameMatcher()) {
            throw new RuntimeException(
                'Method name matcher is already defined, cannot redefine'
            );
        }

        $configurableMethodNames = \array_map(
            static function (ConfigurableMethod $configurable) {
                return \strtolower($configurable->getName());
            },
            $this->configurableMethods
        );

        if (\is_string($constraint) && !\in_array(\strtolower($constraint), $configurableMethodNames, true)) {
            throw new RuntimeException(
                \sprintf(
                    'Trying to configure method "%s" which cannot be configured because it does not exist, has not been specified, is final, or is static',
                    $constraint
                )
            );
        }

        $this->matcher->setMethodNameMatcher(new Matcher\MethodName($constraint));

        return $this;
    }

    /**
     * Validate that a parameters matcher can be defined, throw exceptions otherwise.
     *
     * @throws RuntimeException
     */
    private function canDefineParameters(): void
    {
        if (!$this->matcher->hasMethodNameMatcher()) {
            throw new RuntimeException(
                'Method name matcher is not defined, cannot define parameter ' .
                'matcher without one'
            );
        }

        if ($this->matcher->hasParametersMatcher()) {
            throw new RuntimeException(
                'Parameter matcher is already defined, cannot redefine'
            );
        }
    }

    private function getConfiguredMethod(): ?ConfigurableMethod
    {
        $configuredMethod = null;

        foreach ($this->configurableMethods as $configurableMethod) {
            if ($this->matcher->getMethodNameMatcher()->matchesName($configurableMethod->getName())) {
                if ($configuredMethod !== null) {
                    return null;
                }

                $configuredMethod = $configurableMethod;
            }
        }

        return $configuredMethod;
    }

    private function ensureTypeOfReturnValues(array $values): void
    {
        $configuredMethod = $this->getConfiguredMethod();

        if ($configuredMethod === null) {
            return;
        }

        foreach ($values as $value) {
            if (!$configuredMethod->mayReturn($value)) {
                throw new IncompatibleReturnValueException(
                    \sprintf(
                        'Method %s may not return value of type %s, its return declaration is "%s"',
                        $configuredMethod->getName(),
                        \is_object($value) ? \get_class($value) : \gettype($value),
                        $configuredMethod->getReturnTypeDeclaration()
                    )
                );
            }
        }
    }
}
