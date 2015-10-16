<?php
abstract class AnAbstractClass
{
}

interface AnInterface
{
}

class AssertionOnMockTest extends PHPUnit_Framework_TestCase
{
    public function testOne()
    {
        $stub = $this->getMock('StdClass');
        $this->assertInstanceOf('StdClass', $stub);
    }

    public function testTwo()
    {
        $stub = $this->getMock('AnAbstractClass');
        $this->assertInstanceOf('AnAbstractClass', $stub);
    }

    public function testThree()
    {
        $stub = $this->getMock('AnInterface');
        $this->assertInstanceOf('AnInterface', $stub);
    }
}
