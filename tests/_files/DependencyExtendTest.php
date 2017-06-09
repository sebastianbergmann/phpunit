<?php

class DependencyExtendTest extends DependencyTestCase
{
    /**
     * @depends testB
     */
    public function testC($value)
    {
        $this->assertSame(2, $value);
    }

    public function testA()
    {
        $this->assertTrue(true);
        return 1;
    }
}
