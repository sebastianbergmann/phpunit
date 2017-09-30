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
 * Invocation matcher which checks if a method has been invoked at least
 * N times.
 */
class InvokedAtLeastCount extends InvokedRecorder
{
    /**
     * @var int
     */
    private $requiredInvocations;

    /**
     * @param int $requiredInvocations
     */
    public function __construct($requiredInvocations)
    {
        $this->requiredInvocations = $requiredInvocations;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return 'invoked at least ' . $this->requiredInvocations . ' times';
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

        if ($count < $this->requiredInvocations) {
            throw new ExpectationFailedException(
                'Expected invocation at least ' . $this->requiredInvocations .
                ' times but it occurred ' . $count . ' time(s).'
            );
        }
    }
}
