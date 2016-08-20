<?php
class DependencySuccessTest extends PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        $this->assertTrue(true);
    }

    /**
     * @depends testOne
     */
    public function testTwo()
    {
        $this->assertTrue(true);
    }

    /**
     * @depends DependencySuccessTest::testTwo
     */
    public function testThree()
    {
        $this->assertTrue(true);
    }
}
