<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    /**
     * Exception message
     *
     * @var string
     */
    public const ERROR_MESSAGE = 'Exception message';

    /**
     * Exception message
     *
     * @var string
     */
    public const ERROR_MESSAGE_REGEX = '#regex#';

    /**
     * Exception code
     *
     * @var int
     */
    public const ERROR_CODE = 500;

    /**
     * @expectedException FooBarBaz
     */
    public function testOne(): void
    {
    }

    /**
     * @expectedException Foo_Bar_Baz
     */
    public function testTwo(): void
    {
    }

    /**
     * @expectedException Foo\Bar\Baz
     */
    public function testThree(): void
    {
    }

    /**
     * @expectedException ほげ
     */
    public function testFour(): void
    {
    }

    /**
     * @expectedException Class Message 1234
     */
    public function testFive(): void
    {
    }

    /**
     * @expectedException Class
     * @expectedExceptionMessage Message
     * @expectedExceptionCode 1234
     */
    public function testSix(): void
    {
    }

    /**
     * @expectedException Class
     * @expectedExceptionMessage Message
     * @expectedExceptionCode ExceptionCode
     */
    public function testSeven(): void
    {
    }

    /**
     * @expectedException Class
     * @expectedExceptionMessage Message
     * @expectedExceptionCode 0
     */
    public function testEight(): void
    {
    }

    /**
     * @expectedException Class
     * @expectedExceptionMessage ExceptionTest::ERROR_MESSAGE
     * @expectedExceptionCode ExceptionTest::ERROR_CODE
     */
    public function testNine(): void
    {
    }

    /** @expectedException Class */
    public function testSingleLine(): void
    {
    }

    /**
     * @expectedException Class
     * @expectedExceptionCode ExceptionTest::UNKNOWN_CODE_CONSTANT
     * @expectedExceptionMessage ExceptionTest::UNKNOWN_MESSAGE_CONSTANT
     */
    public function testUnknownConstants(): void
    {
    }

    /**
     * @expectedException Class
     * @expectedExceptionCode 1234
     * @expectedExceptionMessage Message
     * @expectedExceptionMessageRegExp #regex#
     */
    public function testWithRegexMessage(): void
    {
    }

    /**
     * @expectedException Class
     * @expectedExceptionCode 1234
     * @expectedExceptionMessage Message
     * @expectedExceptionMessageRegExp ExceptionTest::ERROR_MESSAGE_REGEX
     */
    public function testWithRegexMessageFromClassConstant(): void
    {
    }

    /**
     * @expectedException Class
     * @expectedExceptionCode 1234
     * @expectedExceptionMessage Message
     * @expectedExceptionMessageRegExp ExceptionTest::UNKNOWN_MESSAGE_REGEX_CONSTANT
     */
    public function testWithUnknowRegexMessageFromClassConstant(): void
    {
    }
}
