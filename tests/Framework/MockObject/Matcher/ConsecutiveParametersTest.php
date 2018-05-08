<?php
/*
 * This file is part of the phpunit-mock-objects package.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\ExpectationFailedException;

class ConsecutiveParametersTest extends TestCase
{
    public function testIntegration()
    {
        $mock = $this->getMockBuilder(stdClass::class)
                     ->setMethods(['foo'])
                     ->getMock();

        $mock->expects($this->any())
             ->method('foo')
             ->withConsecutive(
                 ['bar'],
                 [21, 42]
             );

        $this->assertNull($mock->foo('bar'));
        $this->assertNull($mock->foo(21, 42));
    }

    public function testIntegrationWithLessAssertionsThanMethodCalls()
    {
        $mock = $this->getMockBuilder(stdClass::class)
                     ->setMethods(['foo'])
                     ->getMock();

        $mock->expects($this->any())
             ->method('foo')
             ->withConsecutive(
                 ['bar']
             );

        $this->assertNull($mock->foo('bar'));
        $this->assertNull($mock->foo(21, 42));
    }

    public function testIntegrationExpectingException()
    {
        $mock = $this->getMockBuilder(stdClass::class)
                     ->setMethods(['foo'])
                     ->getMock();

        $mock->expects($this->any())
             ->method('foo')
             ->withConsecutive(
                 ['bar'],
                 [21, 42]
             );

        $mock->foo('bar');

        $this->expectException(ExpectationFailedException::class);

        $mock->foo('invalid');
    }
}
