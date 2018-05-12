<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Matcher;

use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;

/**
 * Records invocations and provides convenience methods for checking them later
 * on.
 * This abstract class can be implemented by matchers which needs to check the
 * number of times an invocation has occurred.
 */
abstract class InvokedRecorder implements Invocation
{
    /**
     * @var BaseInvocation[]
     */
    private $invocations = [];

    /**
     * @return int
     */
    public function getInvocationCount()
    {
        return \count($this->invocations);
    }

    /**
     * @return BaseInvocation[]
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
        return \count($this->invocations) > 0;
    }

    /**
     * @param BaseInvocation $invocation
     */
    public function invoked(BaseInvocation $invocation): void
    {
        $this->invocations[] = $invocation;
    }

    /**
     * @param BaseInvocation $invocation
     *
     * @return bool
     */
    public function matches(BaseInvocation $invocation)
    {
        return true;
    }
}
