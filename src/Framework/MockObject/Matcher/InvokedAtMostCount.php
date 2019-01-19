<?php declare(strict_types=1);
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
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class InvokedAtMostCount extends InvokedRecorder
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
