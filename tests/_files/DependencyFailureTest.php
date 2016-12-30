<?php
use PHPUnit\Framework\TestCase;

class DependencyFailureTest extends TestCase
{
    public function testOne()
    {
        $this->fail();
    }

    /**
     * @depends testOne
     */
    public function testTwo()
    {
        $this->assertTrue(true);
    }

    /**
     * @depends !clone testTwo
     */
    public function testThree()
    {
        $this->assertTrue(true);
    }

    /**
     * @depends clone testOne
     */
    public function testFour()
    {
        $this->assertTrue(true);
    }
}
