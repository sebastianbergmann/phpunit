<?php
class ExceptionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException FooBarBaz
     */
    public function testOne()
    {
    }

    /**
     * @expectedException Foo_Bar_Baz
     */
    public function testTwo()
    {
    }

    /**
     * @expectedException Foo\Bar\Baz
     */
    public function testThree()
    {
    }

    /**
     * @expectedException ほげ
     */
    public function testFour()
    {
    }

    /**
     * @expectedException Class Message 1234
     */
    public function testFive()
    {
    }

    /**
     * @expectedException Class
     * @expectedExceptionMessage Message
     * @expectedExceptionCode 1234
     */
    public function testSix()
    {
    }
}
