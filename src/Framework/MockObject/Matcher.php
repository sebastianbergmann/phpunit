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

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount;
use PHPUnit\Framework\MockObject\Matcher\AnyParameters;
use PHPUnit\Framework\MockObject\Matcher\Invocation as MatcherInvocation;
use PHPUnit\Framework\MockObject\Matcher\InvokedCount;
use PHPUnit\Framework\MockObject\Matcher\MethodName;
use PHPUnit\Framework\MockObject\Matcher\Parameters;
use PHPUnit\Framework\TestFailure;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class Matcher implements MatcherInvocation
{
    /**
     * @var MatcherInvocation
     */
    private $invocationMatcher;

    /**
     * @var mixed
     */
    private $afterMatchBuilderId;

    /**
     * @var bool
     */
    private $afterMatchBuilderIsInvoked = false;

    /**
     * @var MethodName
     */
    private $methodNameMatcher;

    /**
     * @var Parameters
     */
    private $parametersMatcher;

    /**
     * @var Stub
     */
    private $stub;

    public function __construct(MatcherInvocation $invocationMatcher)
    {
        $this->invocationMatcher = $invocationMatcher;
    }

    public function hasMatchers(): bool
    {
        return $this->invocationMatcher !== null && !$this->invocationMatcher instanceof AnyInvokedCount;
    }

    public function hasMethodNameMatcher(): bool
    {
        return $this->methodNameMatcher !== null;
    }

    public function getMethodNameMatcher(): MethodName
    {
        return $this->methodNameMatcher;
    }

    public function setMethodNameMatcher(MethodName $matcher): void
    {
        $this->methodNameMatcher = $matcher;
    }

    public function hasParametersMatcher(): bool
    {
        return $this->parametersMatcher !== null;
    }

    public function getParametersMatcher(): Parameters
    {
        return $this->parametersMatcher;
    }

    public function setParametersMatcher($matcher): void
    {
        $this->parametersMatcher = $matcher;
    }

    public function setStub($stub): void
    {
        $this->stub = $stub;
    }

    public function setAfterMatchBuilderId($id): void
    {
        $this->afterMatchBuilderId = $id;
    }

    /**
     * @throws \Exception
     * @throws RuntimeException
     * @throws ExpectationFailedException
     */
    public function invoked(Invocation $invocation)
    {
        if ($this->invocationMatcher === null) {
            throw new RuntimeException(
                'No invocation matcher is set'
            );
        }

        if ($this->methodNameMatcher === null) {
            throw new RuntimeException('No method matcher is set');
        }

        if ($this->afterMatchBuilderId !== null) {
            $builder = $invocation->getObject()
                                  ->__phpunit_getInvocationMocker()
                                  ->lookupId($this->afterMatchBuilderId);

            if (!$builder) {
                throw new RuntimeException(
                    \sprintf(
                        'No builder found for match builder identification <%s>',
                        $this->afterMatchBuilderId
                    )
                );
            }

            $matcher = $builder->getMatcher();

            if ($matcher && $matcher->invocationMatcher->hasBeenInvoked()) {
                $this->afterMatchBuilderIsInvoked = true;
            }
        }

        $this->invocationMatcher->invoked($invocation);

        try {
            if ($this->parametersMatcher !== null &&
                !$this->parametersMatcher->matches($invocation)) {
                $this->parametersMatcher->verify();
            }
        } catch (ExpectationFailedException $e) {
            throw new ExpectationFailedException(
                \sprintf(
                    "Expectation failed for %s when %s\n%s",
                    $this->methodNameMatcher->toString(),
                    $this->invocationMatcher->toString(),
                    $e->getMessage()
                ),
                $e->getComparisonFailure()
            );
        }

        if ($this->stub) {
            return $this->stub->invoke($invocation);
        }

        return $invocation->generateReturnValue();
    }

    /**
     * @throws RuntimeException
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @return bool
     */
    public function matches(Invocation $invocation)
    {
        if ($this->afterMatchBuilderId !== null) {
            $builder = $invocation->getObject()
                                  ->__phpunit_getInvocationMocker()
                                  ->lookupId($this->afterMatchBuilderId);

            if (!$builder) {
                throw new RuntimeException(
                    \sprintf(
                        'No builder found for match builder identification <%s>',
                        $this->afterMatchBuilderId
                    )
                );
            }

            $matcher = $builder->getMatcher();

            if (!$matcher) {
                return false;
            }

            if (!$matcher->invocationMatcher->hasBeenInvoked()) {
                return false;
            }
        }

        if ($this->invocationMatcher === null) {
            throw new RuntimeException(
                'No invocation matcher is set'
            );
        }

        if ($this->methodNameMatcher === null) {
            throw new RuntimeException('No method matcher is set');
        }

        if (!$this->invocationMatcher->matches($invocation)) {
            return false;
        }

        try {
            if (!$this->methodNameMatcher->matches($invocation)) {
                return false;
            }
        } catch (ExpectationFailedException $e) {
            throw new ExpectationFailedException(
                \sprintf(
                    "Expectation failed for %s when %s\n%s",
                    $this->methodNameMatcher->toString(),
                    $this->invocationMatcher->toString(),
                    $e->getMessage()
                ),
                $e->getComparisonFailure()
            );
        }

        return true;
    }

    /**
     * @throws RuntimeException
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function verify(): void
    {
        if ($this->invocationMatcher === null) {
            throw new RuntimeException(
                'No invocation matcher is set'
            );
        }

        if ($this->methodNameMatcher === null) {
            throw new RuntimeException('No method matcher is set');
        }

        try {
            $this->invocationMatcher->verify();

            if ($this->parametersMatcher === null) {
                $this->parametersMatcher = new AnyParameters;
            }

            $invocationIsAny   = $this->invocationMatcher instanceof AnyInvokedCount;
            $invocationIsNever = $this->invocationMatcher instanceof InvokedCount && $this->invocationMatcher->isNever();

            if (!$invocationIsAny && !$invocationIsNever) {
                $this->parametersMatcher->verify();
            }
        } catch (ExpectationFailedException $e) {
            throw new ExpectationFailedException(
                \sprintf(
                    "Expectation failed for %s when %s.\n%s",
                    $this->methodNameMatcher->toString(),
                    $this->invocationMatcher->toString(),
                    TestFailure::exceptionToString($e)
                )
            );
        }
    }

    public function toString(): string
    {
        $list = [];

        if ($this->invocationMatcher !== null) {
            $list[] = $this->invocationMatcher->toString();
        }

        if ($this->methodNameMatcher !== null) {
            $list[] = 'where ' . $this->methodNameMatcher->toString();
        }

        if ($this->parametersMatcher !== null) {
            $list[] = 'and ' . $this->parametersMatcher->toString();
        }

        if ($this->afterMatchBuilderId !== null) {
            $list[] = 'after ' . $this->afterMatchBuilderId;
        }

        if ($this->stub !== null) {
            $list[] = 'will ' . $this->stub->toString();
        }

        return \implode(' ', $list);
    }
}
