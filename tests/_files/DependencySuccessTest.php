<?php
use PHPUnit\Framework\TestCase;

class DependencySuccessTest extends TestCase
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
