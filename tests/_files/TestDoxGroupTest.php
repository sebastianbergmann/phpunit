<?php

class TestDoxGroupTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group one
     */
    public function testOne()
    {
        $this->assertTrue(true);
    }

    /**
     * @group two
     */
    public function testTwo()
    {
        $this->assertTrue(true);
    }
}
