<?php
class ExceptionTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Error code
	 * 
	 * @var integer
	 */
	const ERROR_CODE = 500;

	/**
	 * Exception error message
	 * 
	 * @var string
	 */
	const ERROR_MESSAGE = 'Exception message';

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
     * @expectedExceptionMessage Message
     * @expectedExceptionCode ExceptionCode
     */
    public function testSeven()
    {
    }

    /**
     * @expectedException Class
     * @expectedExceptionMessage Message
     * @expectedExceptionCode 0
     */
    public function testEight()
    {
    }

    /**
     * @expectedException Class
     * @expectedExceptionMessage @ExceptionTest::ERROR_MESSAGE
     * @expectedExceptionCode @ExceptionTest::ERROR_CODE
     */
    public function testNine()
    {
    }
}
