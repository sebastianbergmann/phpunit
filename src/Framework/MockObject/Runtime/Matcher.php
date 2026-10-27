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

use function assert;
use function sprintf;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\MockObject\Rule\InvokedAtMostCount;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\MockObject\Rule\MethodName;
use PHPUnit\Framework\MockObject\Rule\ParametersRule;
use PHPUnit\Framework\MockObject\Stub\Stub;
use PHPUnit\Util\ThrowableToStringMapper;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 *
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Matcher
{
    private readonly ?InvocationOrder $invocationRule;

    /**
     * @var class-string
     */
    private readonly string $className;

    /**
     * @var ?non-empty-string
     */
    private ?string $afterMatchBuilderId    = null;
    private ?MethodName $methodNameRule     = null;
    private ?ParametersRule $parametersRule = null;
    private ?Stub $stub                     = null;

    /**
     * @param class-string $className
     */
    public function __construct(string $className, ?InvocationOrder $invocationRule = null)
    {
        $this->className      = $className;
        $this->invocationRule = $invocationRule;
    }

    /**
     * @phpstan-assert-if-true !null $this->invocationRule
     */
    public function hasInvocationCountRule(): bool
    {
        return $this->invocationRule !== null;
    }

    /**
     * @phpstan-assert-if-true !null $this->methodNameRule
     */
    public function hasMethodNameRule(): bool
    {
        return $this->methodNameRule !== null;
    }

    /**
     * @throws MethodNameNotConfiguredException
     */
    public function methodNameRule(): MethodName
    {
        if (!$this->hasMethodNameRule()) {
            throw new MethodNameNotConfiguredException;
        }

        return $this->methodNameRule;
    }

    public function setMethodNameRule(MethodName $rule): void
    {
        $this->methodNameRule = $rule;
    }

    /**
     * @phpstan-assert-if-true !null $this->parametersRule
     */
    public function hasParametersRule(): bool
    {
        return $this->parametersRule !== null;
    }

    public function setParametersRule(ParametersRule $rule): void
    {
        $this->parametersRule = $rule;
    }

    public function setStub(Stub $stub): void
    {
        $this->stub = $stub;
    }

    /**
     * @param non-empty-string $id
     */
    public function setAfterMatchBuilderId(string $id): void
    {
        $this->afterMatchBuilderId = $id;
    }

    /**
     * Must only be called for an invocation that matches() returned true for.
     *
     * @throws Exception
     * @throws ExpectationFailedException
     * @throws RuntimeException
     */
    public function invoked(Invocation $invocation): mixed
    {
        assert($this->methodNameRule !== null);

        if ($this->invocationRule !== null) {
            $this->invocationRule->invoked($invocation);
        }

        try {
            $this->parametersRule?->apply($invocation);
        } catch (ExpectationFailedException $e) {
            throw new ExpectationFailedException(
                sprintf(
                    "Expectation for %s failed.\n%s",
                    $this->methodNameRule->failureDescription($this->className),
                    $e->getMessage(),
                ),
                $e->getComparisonFailure(),
            );
        }

        if ($this->stub !== null) {
            return $this->stub->invoke($invocation);
        }

        return $invocation->generateReturnValue();
    }

    /**
     * @throws ExpectationFailedException
     * @throws MatchBuilderNotFoundException
     * @throws MethodNameNotConfiguredException
     * @throws RuntimeException
     */
    public function matches(Invocation $invocation): bool
    {
        if ($this->afterMatchBuilderId !== null) {
            $matcher = $invocation->object()
                ->__phpunit_getInvocationHandler()
                ->lookupMatcher($this->afterMatchBuilderId);

            if ($matcher === null) {
                throw new MatchBuilderNotFoundException($this->afterMatchBuilderId);
            }

            assert($matcher->invocationRule !== null);

            if (!$matcher->invocationRule->hasBeenInvoked()) {
                return false;
            }
        }

        if ($this->methodNameRule === null) {
            throw new MethodNameNotConfiguredException;
        }

        try {
            return $this->methodNameRule->matches($invocation);
        } catch (ExpectationFailedException $e) {
            throw new ExpectationFailedException(
                sprintf(
                    "Expectation for %s failed.\n%s",
                    $this->methodNameRule->failureDescription($this->className),
                    $e->getMessage(),
                ),
                $e->getComparisonFailure(),
            );
        }
    }

    /**
     * @throws ExpectationFailedException
     * @throws MethodNameNotConfiguredException
     */
    public function verify(): void
    {
        if ($this->methodNameRule === null) {
            throw new MethodNameNotConfiguredException;
        }

        if ($this->invocationRule === null) {
            return;
        }

        try {
            $this->invocationRule->verify();
        } catch (ExpectationFailedException) {
            $actual = $this->invocationRule->numberOfInvocations();

            if ($actual === 0) {
                $invoked = 'never invoked';
            } elseif ($actual === 1) {
                $invoked = 'invoked once';
            } else {
                $invoked = sprintf(
                    'invoked %d times',
                    $actual,
                );
            }

            throw new ExpectationFailedException(
                sprintf(
                    '%s was expected to be %s but was %s.',
                    $this->methodNameRule->failureDescription($this->className),
                    $this->invocationRule->toString(),
                    $invoked,
                ),
            );
        }

        if ($this->parametersRule === null) {
            return;
        }

        $invocationIsNever  = $this->invocationRule instanceof InvokedCount && $this->invocationRule->isNever();
        $invocationIsAtMost = $this->invocationRule instanceof InvokedAtMostCount;

        if (!$invocationIsNever && !$invocationIsAtMost) {
            try {
                $this->parametersRule->verify();
            } catch (ExpectationFailedException $e) {
                throw new ExpectationFailedException(
                    sprintf(
                        "Expectation for %s failed.\n%s",
                        $this->methodNameRule->failureDescription($this->className),
                        ThrowableToStringMapper::map($e),
                    ),
                );
            }
        }
    }
}
