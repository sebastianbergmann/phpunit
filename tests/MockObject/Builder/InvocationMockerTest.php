<?php

class Framework_MockObject_Builder_InvocationMockerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException PHPUnit_Framework_MockObject_RuntimeException
     */
    public function testWillReturnWithNoValue()
    {
        $mock = $this->getMock('stdClass', array('foo'));
        $mock
            ->expects($this->any())
            ->method('foo')
            ->willReturn();
    }

    public function testWillReturnWithOneValue()
    {
        $mock = $this->getMock('stdClass', array('foo'));
        $mock
            ->expects($this->any())
            ->method('foo')
            ->willReturn(1);
        $this->assertEquals(1, $mock->foo());
    }

    public function testWillReturnWithMultipleValues()
    {
        $mock = $this->getMock('stdClass', array('foo'));
        $mock
            ->expects($this->any())
            ->method('foo')
            ->willReturn(1, 2, 3);
        $this->assertEquals(1, $mock->foo());
        $this->assertEquals(2, $mock->foo());
        $this->assertEquals(3, $mock->foo());
    }
}
