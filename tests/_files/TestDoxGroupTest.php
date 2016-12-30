<?php

use PHPUnit\Framework\TestCase;

class TestDoxGroupTest extends TestCase
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
