<?php

class Framework_MockObject_Builder_InvocationMockerTest extends PHPUnit_Framework_TestCase
{
    public function testWillReturnWithOneValue()
    {
        $mock = $this->getMock('stdClass', ['foo']);
        $mock
            ->expects($this->any())
            ->method('foo')
            ->willReturn(1);
        $this->assertEquals(1, $mock->foo());
    }

    public function testWillReturnWithMultipleValues()
    {
        $mock = $this->getMock('stdClass', ['foo']);
        $mock
            ->expects($this->any())
            ->method('foo')
            ->willReturn(1, 2, 3);
        $this->assertEquals(1, $mock->foo());
        $this->assertEquals(2, $mock->foo());
        $this->assertEquals(3, $mock->foo());
    }

    public function testWillReturnOnConsecutiveCalls()
    {
        $mock = $this->getMock('stdClass', ['foo']);
        $mock
            ->expects($this->any())
            ->method('foo')
            ->willReturnOnConsecutiveCalls(1, 2, 3);
        $this->assertEquals(1, $mock->foo());
        $this->assertEquals(2, $mock->foo());
        $this->assertEquals(3, $mock->foo());
    }
}
