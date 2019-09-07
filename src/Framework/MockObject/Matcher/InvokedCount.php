<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Rule;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
final class InvokedCount extends InvocationOrder
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

    public function isNever(): bool
    {
        return $this->expectedCount === 0;
    }

    public function toString(): string
    {
        return 'invoked ' . $this->expectedCount . ' time(s)';
    }

    public function matches(BaseInvocation $invocation): bool
    {
        return true;
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

    /**
     * @throws ExpectationFailedException
     */
    protected function invokedDo(BaseInvocation $invocation): void
    {
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
}
