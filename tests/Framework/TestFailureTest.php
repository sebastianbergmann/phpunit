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
 * @since      File available since Release 3.7.20
 */
class Framework_TestFailureTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers PHPUnit_Framework_TestFailure::toString
     */
    public function testToString()
    {
        $test      = new self(__FUNCTION__);
        $exception = new PHPUnit_Framework_Exception('message');
        $failure   = new PHPUnit_Framework_TestFailure($test, $exception);

        $this->assertEquals(__METHOD__ . ': message', $failure->toString());
    }
}
