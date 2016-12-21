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
class PHPUnit_Framework_CodeCoverage_ClassType extends PHPUnit_Framework_CodeCoverage_AbstractChecker
{
    /**
     * @var string
     */
    private $typeName = 'class';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->typeName;
    }

    /**
     * {@inheritdoc}
     */
    public function getCodeCoverage()
    {
        if ( $this->result->getCollectCodeCoverageInformation()) {
            $report = $this->result->getCodeCoverage()->getReport();

            return (int)$report->getTestedClassesPercent(false);
        }

        return false;
    }
}
