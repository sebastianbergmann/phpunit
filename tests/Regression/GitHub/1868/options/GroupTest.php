<?php

class GroupTest extends PHPUnit_Framework_TestCase
{
    /**
     * @group group-1
     */
    public function testIsInGroupOne()
    {
        $this->assertTrue(true);
    }

    /**
     * @group group-2
     */
    public function testIsInGroupOneAndTwo()
    {
        $this->assertTrue(true);
    }

    public function testIsInNoGroup()
    {
        $this->assertTrue(true);
    }
}
