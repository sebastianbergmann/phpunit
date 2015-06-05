<?php

namespace My\Space;

class ExceptionNamespaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Exception message
     *
     * @var string
     */
    const ERROR_MESSAGE = 'Exception namespace message';

    /**
     * Exception code
     *
     * @var integer
     */
    const ERROR_CODE = 200;

    /**
     * @expectedException Class
     * @expectedExceptionMessage ExceptionNamespaceTest::ERROR_MESSAGE
     * @expectedExceptionCode ExceptionNamespaceTest::ERROR_CODE
     */
    public function testConstants()
    {
    }

    /**
     * @expectedException Class
     * @expectedExceptionCode ExceptionNamespaceTest::UNKNOWN_CODE_CONSTANT
     * @expectedExceptionMessage ExceptionNamespaceTest::UNKNOWN_MESSAGE_CONSTANT
     */
    public function testUnknownConstants()
    {
    }

    /**
     * @expectedException Class
     * @expectedExceptionCode Code contains constant ExceptionNamespaceTest::UNKNOWN_CODE_CONSTANT (unlikely)
     * @expectedExceptionMessage Message contains constant ExceptionNamespaceTest::UNKNOWN_MESSAGE_CONSTANT
     */
    public function testConstantInsideValue()
    {
    }

    /**
     * @expectedException \Class
     * @expectedExceptionMessage \My\Space\ExceptionNamespaceTest::ERROR_MESSAGE
     * @expectedExceptionCode \My\Space\ExceptionNamespaceTest::ERROR_CODE
     */
    public function testFullyQualified()
    {
    }
}
