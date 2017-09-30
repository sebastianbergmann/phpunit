<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount;
use PHPUnit\Framework\MockObject\Matcher\InvokedCount;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\MockObject\Matcher\Invocation as MatcherInvocation;
use PHPUnit\Framework\MockObject\Matcher\MethodName;
use PHPUnit\Framework\MockObject\Matcher\Parameters;
use PHPUnit\Framework\MockObject\Matcher\AnyParameters;
use PHPUnit\Framework\MockObject\Exception\RuntimeException;

/**
 * Main matcher which defines a full expectation using method, parameter and
 * invocation matchers.
 * This matcher encapsulates all the other matchers and allows the builder to
 * set the specific matchers when the appropriate methods are called (once(),
 * where() etc.).
 *
 * All properties are public so that they can easily be accessed by the builder.
 */
class Matcher implements MatcherInvocation
{
    /**
     * @var MatcherInvocation
     */
    public $invocationMatcher;

    /**
     * @var mixed
     */
    public $afterMatchBuilderId = null;

    /**
     * @var bool
     */
    public $afterMatchBuilderIsInvoked = false;

    /**
     * @var MethodName
     */
    public $methodNameMatcher = null;

    /**
     * @var Parameters
     */
    public $parametersMatcher = null;

    /**
     * @var Stub
     */
    public $stub = null;

    /**
     * @param MatcherInvocation $invocationMatcher
     */
    public function __construct(MatcherInvocation $invocationMatcher)
    {
        $this->invocationMatcher = $invocationMatcher;
    }

    /**
     * @return string
     */
    public function toString()
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

        return implode(' ', $list);
    }

    /**
     * @param Invocation $invocation
     *
     * @return mixed
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
            $builder = $invocation->object
                                  ->__phpunit_getInvocationMocker()
                                  ->lookupId($this->afterMatchBuilderId);

            if (!$builder) {
                throw new RuntimeException(
                    sprintf(
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
                sprintf(
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
     * @param Invocation $invocation
     *
     * @return bool
     */
    public function matches(Invocation $invocation)
    {
        if ($this->afterMatchBuilderId !== null) {
            $builder = $invocation->object
                                  ->__phpunit_getInvocationMocker()
                                  ->lookupId($this->afterMatchBuilderId);

            if (!$builder) {
                throw new RuntimeException(
                    sprintf(
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
                sprintf(
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
     */
    public function verify()
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
                sprintf(
                    "Expectation failed for %s when %s.\n%s",
                    $this->methodNameMatcher->toString(),
                    $this->invocationMatcher->toString(),
                    TestFailure::exceptionToString($e)
                )
            );
        }
    }

    public function hasMatchers()
    {
        if ($this->invocationMatcher !== null &&
            !$this->invocationMatcher instanceof AnyInvokedCount) {
            return true;
        }

        return false;
    }
}
