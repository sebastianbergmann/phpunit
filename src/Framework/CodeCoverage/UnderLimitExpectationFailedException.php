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
 * Exception for code coverage under limit expectations which failed their check.
 *
 * The exception contains the error message and the
 * SebastianBergmann\Comparator\ComparisonFailure which is used to
 * generate diff output of the failed expectations.
 */
class PHPUnit_Framework_CodeCoverage_UnderLimitExpectationFailedException extends PHPUnit_Framework_ExpectationFailedException
{
    /**
     * @param string $metric
     * @param integer $codeCoverageLimit
     * @param integer $codeCoverage
     */
    public function __construct($metric, $codeCoverageLimit, $codeCoverage)
    {
        $comparison = new \SebastianBergmann\Comparator\ComparisonFailure(
            $codeCoverageLimit,
            $codeCoverage,
            $codeCoverageLimit,
            $codeCoverage
        );

        parent::__construct("The $metric code coverage is under limit.", $comparison);
    }
}
