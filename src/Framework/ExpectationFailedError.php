<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Thrown when an assertion failed.
 *
 * @since Class available since Release 2.0.0
 */
class PHPUnit_Framework_ExpectationFailedError extends PHPUnit_Framework_AssertionFailedError
{

    protected $failedExpectations = [];

    public function __construct(array $failedExpectations)
    {

        $this->failedExpectations = $failedExpectations;
        parent::__construct("Failed Expectations");
    }

    public function getFailedExpectations()
    {
        return $this->failedExpectations;
    }

    /**
     * Wrapper for getMessage() which is declared as final.
     *
     * @return string
     */
    public function toString()
    {
        $message = $this->getMessage();
        foreach ($this->failedExpectations as $key => $failed) {
            $message .= "\n{$key}: " . $failed;
        }
        return $message;
    }
}
