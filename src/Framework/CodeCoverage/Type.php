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
 * Defines the contract of an implementation of the code coverage checkers
 */
interface PHPUnit_Framework_CodeCoverage_Type
{
    /**
     * Checks if the code coverage is under a given limit.
     *
     * @param integer $codeCoverageLimit
     */
    public function isUnderLimit($codeCoverageLimit);

    /**
     * Retrieve the name of the type code coverage checker.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the amount of code coverage for the implemented type of metric.
     *
     * @return integer
     */
    public function getCodeCoverage();
}
