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
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;

/**
 * Invocation matcher which checks if a method has been invoked a certain amount
 * of times.
 * If the number of invocations exceeds the value it will immediately throw an
 * exception,
 * If the number is less it will later be checked in verify() and also throw an
 * exception.
 */
class InvokedCount extends InvokedRecorder
{
    /**
     * @var int
     */
    private $expectedCount;

    /**
     * @param int $expectedCount
     */
    public function __construct($expectedCount)
    {
        $this->expectedCount = $expectedCount;
    }

    /**
     * @return bool
     */
    public function isNever()
    {
        return $this->expectedCount === 0;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        return 'invoked ' . $this->expectedCount . ' time(s)';
    }

    /**
     * @param BaseInvocation $invocation
     *
     * @throws ExpectationFailedException
     */
    public function invoked(BaseInvocation $invocation): void
    {
        parent::invoked($invocation);

        $count = $this->getInvocationCount();

        if ($count > $this->expectedCount) {
            $message = $invocation->toString() . ' ';

            switch ($this->expectedCount) {
                case 0:
                    $message .= 'was not expected to be called.';

                    break;

                case 1:
                    $message .= 'was not expected to be called more than once.';

                    break;

                default:
                    $message .= \sprintf(
                        'was not expected to be called more than %d times.',
                        $this->expectedCount
                    );
            }

            throw new ExpectationFailedException($message);
        }
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

        if ($count !== $this->expectedCount) {
            throw new ExpectationFailedException(
                \sprintf(
                    'Method was expected to be called %d times, ' .
                    'actually called %d times.',
                    $this->expectedCount,
                    $count
                )
            );
        }
    }
}
