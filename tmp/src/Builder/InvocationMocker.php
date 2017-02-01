<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPUnit\Framework\MockObject\Builder;

use Exception;
use PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit_Framework_Constraint;
use PHPUnit_Framework_MockObject_Matcher_AnyParameters;
use PHPUnit_Framework_MockObject_Matcher_ConsecutiveParameters;
use PHPUnit_Framework_MockObject_Matcher_Invocation;
use PHPUnit_Framework_MockObject_Matcher_MethodName;
use PHPUnit_Framework_MockObject_Matcher_Parameters;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls;
use PHPUnit_Framework_MockObject_Stub_Exception;
use PHPUnit_Framework_MockObject_Stub_MatcherCollection;
use PHPUnit_Framework_MockObject_Stub_Return;
use PHPUnit_Framework_MockObject_Stub_ReturnArgument;
use PHPUnit_Framework_MockObject_Stub_ReturnCallback;
use PHPUnit_Framework_MockObject_Stub_ReturnReference;
use PHPUnit_Framework_MockObject_Stub_ReturnSelf;
use PHPUnit_Framework_MockObject_Stub_ReturnValueMap;

/**
 * Builder for mocked or stubbed invocations.
 *
 * Provides methods for building expectations without having to resort to
 * instantiating the various matchers manually. These methods also form a
 * more natural way of reading the expectation. This class should be together
 * with the test case PHPUnit_Framework_MockObject_TestCase.
 *
 * @since Class available since Release 1.0.0
 */
class InvocationMocker implements MethodNameMatch
{
    /**
     * @var PHPUnit_Framework_MockObject_Stub_MatcherCollection
     */
    protected $collection;

    /**
     * @var Matcher
     */
    protected $matcher;

    /**
     * @var string[]
     */
    private $configurableMethods = [];

    /**
     * @param PHPUnit_Framework_MockObject_Stub_MatcherCollection $collection
     * @param PHPUnit_Framework_MockObject_Matcher_Invocation     $invocationMatcher
     * @param array                                               $configurableMethods
     */
    public function __construct(PHPUnit_Framework_MockObject_Stub_MatcherCollection $collection, PHPUnit_Framework_MockObject_Matcher_Invocation $invocationMatcher, array $configurableMethods)
    {
        $this->collection = $collection;
        $this->matcher    = new Matcher(
            $invocationMatcher
        );

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
        $this->matcher->stub = $stub;

        return $this;
    }

    /**
     * @param mixed $value
     * @param mixed $nextValues , ...
     *
     * @return InvocationMocker
     */
    public function willReturn($value, ...$nextValues)
    {
        $stub = count($nextValues) === 0 ?
            new PHPUnit_Framework_MockObject_Stub_Return($value) :
            new PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls(
                array_merge([$value], $nextValues)
            );

        return $this->will($stub);
    }

    /**
     * @param mixed $reference
     *
     * @return InvocationMocker
     */
    public function willReturnReference(&$reference)
    {
        $stub = new PHPUnit_Framework_MockObject_Stub_ReturnReference($reference);

        return $this->will($stub);
    }

    /**
     * @param array $valueMap
     *
     * @return InvocationMocker
     */
    public function willReturnMap(array $valueMap)
    {
        $stub = new PHPUnit_Framework_MockObject_Stub_ReturnValueMap(
            $valueMap
        );

        return $this->will($stub);
    }

    /**
     * @param mixed $argumentIndex
     *
     * @return InvocationMocker
     */
    public function willReturnArgument($argumentIndex)
    {
        $stub = new PHPUnit_Framework_MockObject_Stub_ReturnArgument(
            $argumentIndex
        );

        return $this->will($stub);
    }

    /**
     * @param callable $callback
     *
     * @return InvocationMocker
     */
    public function willReturnCallback($callback)
    {
        $stub = new PHPUnit_Framework_MockObject_Stub_ReturnCallback(
            $callback
        );

        return $this->will($stub);
    }

    /**
     * @return InvocationMocker
     */
    public function willReturnSelf()
    {
        $stub = new PHPUnit_Framework_MockObject_Stub_ReturnSelf;

        return $this->will($stub);
    }

    /**
     * @param mixed $values , ...
     *
     * @return InvocationMocker
     */
    public function willReturnOnConsecutiveCalls(...$values)
    {
        $stub = new PHPUnit_Framework_MockObject_Stub_ConsecutiveCalls($values);

        return $this->will($stub);
    }

    /**
     * @param Exception $exception
     *
     * @return InvocationMocker
     */
    public function willThrowException(Exception $exception)
    {
        $stub = new PHPUnit_Framework_MockObject_Stub_Exception($exception);

        return $this->will($stub);
    }

    /**
     * @param mixed $id
     *
     * @return InvocationMocker
     */
    public function after($id)
    {
        $this->matcher->afterMatchBuilderId = $id;

        return $this;
    }

    /**
     * Validate that a parameters matcher can be defined, throw exceptions otherwise.
     *
     * @throws RuntimeException
     */
    private function canDefineParameters()
    {
        if ($this->matcher->methodNameMatcher === null) {
            throw new RuntimeException(
                'Method name matcher is not defined, cannot define parameter ' .
                'matcher without one'
            );
        }

        if ($this->matcher->parametersMatcher !== null) {
            throw new RuntimeException(
                'Parameter matcher is already defined, cannot redefine'
            );
        }
    }

    /**
     * @param  array ...$arguments
     *
     * @return InvocationMocker
     */
    public function with(...$arguments)
    {
        $this->canDefineParameters();

        $this->matcher->parametersMatcher = new PHPUnit_Framework_MockObject_Matcher_Parameters($arguments);

        return $this;
    }

    /**
     * @param  array ...$arguments
     *
     * @return InvocationMocker
     */
    public function withConsecutive(...$arguments)
    {
        $this->canDefineParameters();

        $this->matcher->parametersMatcher = new PHPUnit_Framework_MockObject_Matcher_ConsecutiveParameters($arguments);

        return $this;
    }

    /**
     * @return InvocationMocker
     */
    public function withAnyParameters()
    {
        $this->canDefineParameters();

        $this->matcher->parametersMatcher = new PHPUnit_Framework_MockObject_Matcher_AnyParameters;

        return $this;
    }

    /**
     * @param PHPUnit_Framework_Constraint|string $constraint
     *
     * @return InvocationMocker
     */
    public function method($constraint)
    {
        if ($this->matcher->methodNameMatcher !== null) {
            throw new RuntimeException(
                'Method name matcher is already defined, cannot redefine'
            );
        }

        if (is_string($constraint) && !in_array(strtolower($constraint), $this->configurableMethods)) {
            throw new RuntimeException(
                sprintf(
                    'Trying to configure method "%s" which cannot be configured because it does not exist, has not been specified, is final, or is static',
                    $constraint
                )
            );
        }

        $this->matcher->methodNameMatcher = new PHPUnit_Framework_MockObject_Matcher_MethodName($constraint);

        return $this;
    }
}
