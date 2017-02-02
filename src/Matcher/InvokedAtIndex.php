<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\ExpectationFailedException;

/**
 * Invocation matcher which checks if a method was invoked at a certain index.
 *
 * If the expected index number does not match the current invocation index it
 * will not match which means it skips all method and parameter matching. Only
 * once the index is reached will the method and parameter start matching and
 * verifying.
 *
 * If the index is never reached it will throw an exception in index.
 */
class PHPUnit_Framework_MockObject_Matcher_InvokedAtIndex implements PHPUnit_Framework_MockObject_Matcher_Invocation
{
    /**
     * @var int
     */
    protected $sequenceIndex;

    /**
     * @var int
     */
    protected $currentIndex = -1;

    /**
     * @param int $sequenceIndex
     */
    public function __construct($sequenceIndex)
    {
        $this->sequenceIndex = $sequenceIndex;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return 'invoked at sequence index ' . $this->sequenceIndex;
    }

    /**
     * @param PHPUnit_Framework_MockObject_Invocation $invocation
     *
     * @return bool
     */
    public function matches(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
        $this->currentIndex++;

        return $this->currentIndex == $this->sequenceIndex;
    }

    /**
     * @param PHPUnit_Framework_MockObject_Invocation $invocation
     */
    public function invoked(PHPUnit_Framework_MockObject_Invocation $invocation)
    {
    }

    /**
     * Verifies that the current expectation is valid. If everything is OK the
     * code should just return, if not it must throw an exception.
     *
     * @throws ExpectationFailedException
     */
    public function verify()
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
}
