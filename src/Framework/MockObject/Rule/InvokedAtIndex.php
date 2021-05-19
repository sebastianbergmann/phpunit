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

use function sprintf;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\MockObject\Invocation as BaseInvocation;

/**
 * @internal This class is not covered by the backward compatibility promise for PHPUnit
 */
class InvokedAtIndex extends InvocationOrder
{
    /**
     * @var int
     */
    private $sequenceIndex;

    /**
     * @var int
     */
    private $currentIndex = -1;

    /**
     * @param int $sequenceIndex
     */
    public function __construct($sequenceIndex)
    {
        $this->sequenceIndex = $sequenceIndex;
    }

    public function toString(): string
    {
        return 'invoked at sequence index ' . $this->sequenceIndex;
    }

    public function matches(BaseInvocation $invocation): bool
    {
        $this->currentIndex++;

        return $this->currentIndex == $this->sequenceIndex;
    }

    /**
     * Verifies that the current expectation is valid. If everything is OK the
     * code should just return, if not it must throw an exception.
     *
     * @throws ExpectationFailedException
     */
    public function verify(): void
    {
        if ($this->currentIndex < $this->sequenceIndex) {
            throw new ExpectationFailedException(
                sprintf(
                    'The expected invocation at index %s was never reached.',
                    $this->sequenceIndex
                )
            );
        }
    }

    protected function invokedDo(BaseInvocation $invocation): void
    {
    }
}
