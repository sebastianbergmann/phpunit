<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Matcher;

use PHPUnit\Framework\ExpectationFailedException;

/**
 * Invocation matcher which checks if a method has been invoked at least one
 * time.
 *
 * If the number of invocations is 0 it will throw an exception in verify.
 */
class InvokedAtLeastOnce extends InvokedRecorder
{
    /**
     * @return string
     */
    public function toString()
    {
        return 'invoked at least once';
    }

    /**
     * Verifies that the current expectation is valid. If everything is OK the
     * code should just return, if not it must throw an exception.
     *
     * @throws ExpectationFailedException
     */
    public function verify()
    {
        $count = $this->getInvocationCount();

        if ($count < 1) {
            throw new ExpectationFailedException(
                'Expected invocation at least once but it never occurred.'
            );
        }
    }
}
