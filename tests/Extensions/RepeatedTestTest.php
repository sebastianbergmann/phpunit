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
 * @since      Class available since Release 2.0.0
 * @covers     PHPUnit_Extensions_RepeatedTest
 */
class Extensions_RepeatedTestTest extends PHPUnit_Framework_TestCase
{
    protected $suite;

    public function __construct()
    {
        $this->suite = new PHPUnit_Framework_TestSuite;

        $this->suite->addTest(new Success);
        $this->suite->addTest(new Success);
    }

    public function testRepeatedOnce()
    {
        $test = new PHPUnit_Extensions_RepeatedTest($this->suite, 1);
        $this->assertEquals(2, count($test));

        $result = $test->run();
        $this->assertEquals(2, count($result));
    }

    public function testRepeatedMoreThanOnce()
    {
        $test = new PHPUnit_Extensions_RepeatedTest($this->suite, 3);
        $this->assertEquals(6, count($test));

        $result = $test->run();
        $this->assertEquals(6, count($result));
    }

    /**
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testRepeatedNegative()
    {
        new PHPUnit_Extensions_RepeatedTest($this->suite, -1);
    }
}
