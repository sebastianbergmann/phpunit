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

use PHPUnit\Framework\ExpectationFailedException;

/**
 * Invocation matcher which checks if a method has been invoked at least
 * N times.
 */
class InvokedAtMostCount extends InvokedRecorder
{
    /**
     * @var int
     */
    private $allowedInvocations;

    /**
     * @param int $allowedInvocations
     */
    public function __construct($allowedInvocations)
    {
        $this->allowedInvocations = $allowedInvocations;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return 'invoked at most ' . $this->allowedInvocations . ' times';
    }

    /**
     * Verifies that the current expectation is valid. If everything is OK the
     * code should just return, if not it must throw an exception.
     *
     * @throws ExpectationFailedException
     */
    public function verify(): void
    {
        $count = $this->getInvocationCount();

        if ($count > $this->allowedInvocations) {
            throw new ExpectationFailedException(
                'Expected invocation at most ' . $this->allowedInvocations .
                ' times but it occurred ' . $count . ' time(s).'
            );
        }
    }
}
