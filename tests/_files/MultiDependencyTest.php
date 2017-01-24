<?php
use PHPUnit\Framework\TestCase;

class MultiDependencyTest extends TestCase
{
    public function testOne()
    {
        $this->assertTrue(true);

        return 'foo';
    }

    public function testTwo()
    {
        $this->assertTrue(true);

        return 'bar';
    }

    /**
     * @depends testOne
     * @depends testTwo
     */
    public function testThree($a, $b)
    {
        $this->assertEquals('foo', $a);
        $this->assertEquals('bar', $b);
    }
}
