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
 * Exception for expectations which failed their check.
 *
 * The exception contains the error message and optionally a
 * SebastianBergmann\Comparator\ComparisonFailure which is used to
 * generate diff output of the failed expectations.
 *
 * @since Class available since Release 3.0.0
 */
class PHPUnit_Framework_CodeCoverageUnderLimitExpectationFailedException extends PHPUnit_Framework_ExpectationFailedException
{
    /**
     * @param string $metric
     * @param integer $codeCoverageLimit
     * @param integer $codeCoverage
     */
    public function __construct($metric, $codeCoverageLimit, $codeCoverage)
    {
        parent::__construct("$metric coverage under limit. Expected: $codeCoverageLimit Current: $codeCoverage");
    }
}
