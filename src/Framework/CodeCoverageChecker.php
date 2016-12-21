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
class PHPUnit_Framework_CodeCoverageChecker
{
    /**
     * @var PHPUnit_Framework_Test
     */
    private $suite;
    /**
     * @var PHPUnit_Framework_TestResult
     */
    private $result;

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
     *
     * @return PHPUnit_Framework_TestResult
     */
    public function isCodeCoverageUnderLimit($codeCoverageLimit)
    {
        if ( $this->result->getCollectCodeCoverageInformation()) {
            $report = $this->result->getCodeCoverage()->getReport();

            $this->isCodeCoverageOfLinesUnderLimit($codeCoverageLimit, $report);
            $this->isCodeCoverageOfClassesUnderLimit($codeCoverageLimit, $report);
            $this->isCodeCoverageOfMethodsUnderLimit($codeCoverageLimit, $report);
        }
    }

    /**
     * @param int $codeCoverageLimit
     * @param Directory $report
     */
    private function isCodeCoverageOfLinesUnderLimit($codeCoverageLimit, Directory $report)
    {
        $this->isUnderCodeCoverageLimit(
            $codeCoverageLimit,
            (int)$report->getLineExecutedPercent(false),
            'line'
        );
    }

    /**
     * @param int $codeCoverageLimit
     * @param Directory $report
     */
    private function isCodeCoverageOfClassesUnderLimit($codeCoverageLimit, Directory $report)
    {
        $this->isUnderCodeCoverageLimit(
            $codeCoverageLimit,
            (int)$report->getTestedClassesPercent(false),
            'class'
        );
    }

    /**
     * @param int $codeCoverageLimit
     * @param Directory $report
     */
    private function isCodeCoverageOfMethodsUnderLimit($codeCoverageLimit, Directory $report)
    {
        $this->isUnderCodeCoverageLimit(
            $codeCoverageLimit,
            (int)$report->getTestedMethodsPercent(false),
            'method'
        );
    }

    /**
     * Add failure if the $codeCoverage is under $codeCoverageLimit.
     *
     * @param integer $codeCoverageLimit
     * @param integer $codeCoverage
     * @param string $metric
     *
     * @return PHPUnit_Framework_TestResult
     */
    private function isUnderCodeCoverageLimit(
        $codeCoverageLimit,
        $codeCoverage,
        $metric
    )
    {
        if ($codeCoverageLimit > $codeCoverage) {
            $this->result->addFailure(
                $this->suite,
                new PHPUnit_Framework_CodeCoverageUnderLimitExpectationFailedException(
                    $metric,
                    $codeCoverageLimit,
                    $codeCoverage
                ),
                $this->result->time()
            );
        }
    }
}
