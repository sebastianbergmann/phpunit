<?php
class ExceptionTest extends PHPUnit_Framework_TestCase
{
    const CODE_ONE = 12345;

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

    /**
     * @expectedException Class
     * @expectedExceptionCode ExceptionTest::CODE_ONE
     */
    public function testSeven()
    {
    }
}
