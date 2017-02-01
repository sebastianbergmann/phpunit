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
 * Invocation matcher which allows any parameters to a method.
 */
class PHPUnit_Framework_MockObject_Matcher_AnyParameters extends PHPUnit_Framework_MockObject_Matcher_StatelessInvocation
{
    /**
     * @return string
     */
    public function toString()
    {
        return 'with any parameters';
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
