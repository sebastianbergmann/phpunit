<?php
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
use PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\MockObject\Matcher\Invocation;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\MockObject\Stub\MatcherCollection;

/**
 * Builder for mocked or stubbed invocations.
 *
 * Provides methods for building expectations without having to resort to
 * instantiating the various matchers manually. These methods also form a
 * more natural way of reading the expectation. This class should be together
 * with the test case PHPUnit\Framework\MockObject\TestCase.
 */
class InvocationMocker implements MethodNameMatch
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
     * @var string[]
     */
    private $configurableMethods;

    /**
     * @param MatcherCollection $collection
     * @param Invocation        $invocationMatcher
     * @param array             $configurableMethods
     */
    public function __construct(MatcherCollection $collection, Invocation $invocationMatcher, array $configurableMethods)
    {
        $this->collection = $collection;
        $this->matcher    = new Matcher($invocationMatcher);

        $this->collection->addMatcher($this->matcher);

        $this->configurableMethods = $configurableMethods;
    }

    /**
     * @return Matcher
     */
    public function getMatcher()
    {
        return $this->matcher;
    }

    /**
     * @param mixed $id
     *
     * @return InvocationMocker
     */
    public function id($id)
    {
        $this->collection->registerId($id, $this);

        return $this;
    }

    /**
     * @param Stub $stub
     *
     * @return InvocationMocker
     */
    public function will(Stub $stub)
    {
        $this->matcher->setStub($stub);

        return $this;
    }

    /**
     * @param mixed $value
     * @param mixed $nextValues, ...
     *
     * @return InvocationMocker
     */
    public function willReturn($value, ...$nextValues)
    {
        if (\count($nextValues) === 0) {
            $stub = new Stub\ReturnStub($value);
        } else {
            $stub = new Stub\ConsecutiveCalls(
                \array_merge([$value], $nextValues)
            );
        }

        return $this->will($stub);
    }

    /**
     * @param mixed $reference
     *
     * @return InvocationMocker
     */
    public function willReturnReference(&$reference)
    {
        $stub = new Stub\ReturnReference($reference);

        return $this->will($stub);
    }

    /**
     * @param array $valueMap
     *
     * @return InvocationMocker
     */
    public function willReturnMap(array $valueMap)
    {
        $stub = new Stub\ReturnValueMap($valueMap);

        return $this->will($stub);
    }

    /**
     * @param mixed $argumentIndex
     *
     * @return InvocationMocker
     */
    public function willReturnArgument($argumentIndex)
    {
        $stub = new Stub\ReturnArgument($argumentIndex);

        return $this->will($stub);
    }

    /**
     * @param callable $callback
     *
     * @return InvocationMocker
     */
    public function willReturnCallback($callback)
    {
        $stub = new Stub\ReturnCallback($callback);

        return $this->will($stub);
    }

    /**
     * @return InvocationMocker
     */
    public function willReturnSelf()
    {
        $stub = new Stub\ReturnSelf;

        return $this->will($stub);
    }

    /**
     * @param mixed $values, ...
     *
     * @return InvocationMocker
     */
    public function willReturnOnConsecutiveCalls(...$values)
    {
        $stub = new Stub\ConsecutiveCalls($values);

        return $this->will($stub);
    }

    /**
     * @param \Exception $exception
     *
     * @return InvocationMocker
     */
    public function willThrowException(\Exception $exception)
    {
        $stub = new Stub\Exception($exception);

        return $this->will($stub);
    }

    /**
     * @param mixed $id
     *
     * @return InvocationMocker
     */
    public function after($id)
    {
        $this->matcher->setAfterMatchBuilderId($id);

        return $this;
    }

    /**
     * @param array ...$arguments
     *
     * @throws RuntimeException
     *
     * @return InvocationMocker
     */
    public function with(...$arguments)
    {
        $this->canDefineParameters();

        $this->matcher->setParametersMatcher(new Matcher\Parameters($arguments));

        return $this;
    }

    /**
     * @param array ...$arguments
     *
     * @throws RuntimeException
     *
     * @return InvocationMocker
     */
    public function withConsecutive(...$arguments)
    {
        $this->canDefineParameters();

        $this->matcher->setParametersMatcher(new Matcher\ConsecutiveParameters($arguments));

        return $this;
    }

    /**
     * @throws RuntimeException
     *
     * @return InvocationMocker
     */
    public function withAnyParameters()
    {
        $this->canDefineParameters();

        $this->matcher->setParametersMatcher(new Matcher\AnyParameters);

        return $this;
    }

    /**
     * @param Constraint|string $constraint
     *
     * @throws RuntimeException
     *
     * @return InvocationMocker
     */
    public function method($constraint)
    {
        if ($this->matcher->hasMethodNameMatcher()) {
            throw new RuntimeException(
                'Method name matcher is already defined, cannot redefine'
            );
        }

        if (\is_string($constraint) && !\in_array(\strtolower($constraint), $this->configurableMethods, true)) {
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
}
