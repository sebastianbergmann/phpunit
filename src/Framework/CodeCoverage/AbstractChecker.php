<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SebastianBergmann\CodeCoverage\Node\Directory;

/**
 * Checks the code coverage for a given TestResult.
 *
 * The exception contains the error message and optionally a
 * SebastianBergmann\Comparator\ComparisonFailure which is used to
 * generate diff output of the failed expectations.
 */
abstract class PHPUnit_Framework_CodeCoverage_AbstractChecker implements PHPUnit_Framework_CodeCoverage_Type
{
    /**
     * @var PHPUnit_Framework_Test
     */
    private $suite;

    /**
     * @var PHPUnit_Framework_TestResult
     */
    protected $result;

    public function __construct(
        PHPUnit_Framework_Test $suite,
        PHPUnit_Framework_TestResult $result
    )
    {
        $this->suite = $suite;
        $this->result = $result;
    }

    /**
     * Check the code coverage is not under the expected limit.
     *
     * @param integer $codeCoverageLimit
     */
    public function isUnderLimit($codeCoverageLimit)
    {
        if ($this->result->getCollectCodeCoverageInformation()) {
            $codeCoverage = $this->getCodeCoverage();
            if ($codeCoverageLimit > $codeCoverage) {
                $this->result->addFailure(
                    $this->suite,
                    new PHPUnit_Framework_CodeCoverage_UnderLimitExpectationFailedException(
                        $this->getName(),
                        $codeCoverageLimit,
                        $codeCoverage
                    ),
                    $this->result->time()
                );
            }
        }
    }
}
