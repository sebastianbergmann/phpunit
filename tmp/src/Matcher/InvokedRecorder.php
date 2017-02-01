<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\MockObject\Invocation;

/**
 * Records invocations and provides convenience methods for checking them later
 * on.
 * This abstract class can be implemented by matchers which needs to check the
 * number of times an invocation has occurred.
 *
 * @abstract
 */
abstract class PHPUnit_Framework_MockObject_Matcher_InvokedRecorder implements PHPUnit_Framework_MockObject_Matcher_Invocation
{
    /**
     * @var Invocation[]
     */
    protected $invocations = [];

    /**
     * @return int
     */
    public function getInvocationCount()
    {
        return count($this->invocations);
    }

    /**
     * @return Invocation[]
     */
    public function getInvocations()
    {
        return $this->invocations;
    }

    /**
     * @return bool
     */
    public function hasBeenInvoked()
    {
        return count($this->invocations) > 0;
    }

    /**
     * @param Invocation $invocation
     */
    public function invoked(Invocation $invocation)
    {
        $this->invocations[] = $invocation;
    }

    /**
     * @param Invocation $invocation
     *
     * @return bool
     */
    public function matches(Invocation $invocation)
    {
        return true;
    }
}
