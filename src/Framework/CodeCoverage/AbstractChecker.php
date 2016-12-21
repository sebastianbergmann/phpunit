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
 * Checks the code coverage for a given TestResult and Test.
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

    /**
     * PHPUnit_Framework_CodeCoverage_AbstractChecker constructor.
     *
     * @param PHPUnit_Framework_Test $suite
     * @param PHPUnit_Framework_TestResult $result
     */
    public function __construct(
        PHPUnit_Framework_Test $suite,
        PHPUnit_Framework_TestResult $result
    )
    {
        $this->suite = $suite;
        $this->result = $result;
    }

    /**
     * {@inheritdoc}
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
