<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework;

use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * Exception for expectations which failed their check.
 *
 * The exception contains the error message and optionally a
 * SebastianBergmann\Comparator\ComparisonFailure which is used to
 * generate diff output of the failed expectations.
 */
class ExpectationFailedException extends AssertionFailedError
{
    protected $comparisonFailure;

    /**
     * @param string                 $message
     * @param null|ComparisonFailure $comparisonFailure
     * @param null|\Exception        $previous
     */
    public function __construct($message, ComparisonFailure $comparisonFailure = null, \Exception $previous = null)
    {
        $this->comparisonFailure = $comparisonFailure;

        parent::__construct($message, 0, $previous);
    }

    /**
     * @return null|ComparisonFailure
     */
    public function getComparisonFailure(): ?ComparisonFailure
    {
        return $this->comparisonFailure;
    }
}
