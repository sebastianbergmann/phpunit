<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestSuite;

/**
 * @since Class available since Release 3.4.0
 */
class PHPUnit_Framework_TestSuite_DataProvider extends TestSuite
{
    /**
     * Sets the dependencies of a TestCase.
     *
     * @param array $dependencies
     */
    public function setDependencies(array $dependencies)
    {
        foreach ($this->tests as $test) {
            $test->setDependencies($dependencies);
        }
    }
}
