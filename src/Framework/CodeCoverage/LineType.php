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
 * Checks the code coverage of the number of lines.
 */
class PHPUnit_Framework_CodeCoverage_LineType extends PHPUnit_Framework_CodeCoverage_AbstractChecker
{
    /**
     * @var string
     */
    private $typeName = 'line';

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

            return (int)$report->getLineExecutedPercent(false);
        }

        return false;
    }
}
