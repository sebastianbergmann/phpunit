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

class InvocationMockerTest extends TestCase
{
    public function testWillReturnWithOneValue()
    {
        $mock = $this->getMockBuilder(stdClass::class)
                     ->setMethods(['foo'])
                     ->getMock();

        $mock->expects($this->any())
             ->method('foo')
             ->willReturn(1);

        $this->assertEquals(1, $mock->foo());
    }

    public function testWillReturnWithMultipleValues()
    {
        $mock = $this->getMockBuilder(stdClass::class)
                     ->setMethods(['foo'])
                     ->getMock();

        $mock->expects($this->any())
             ->method('foo')
             ->willReturn(1, 2, 3);

        $this->assertEquals(1, $mock->foo());
        $this->assertEquals(2, $mock->foo());
        $this->assertEquals(3, $mock->foo());
    }

    public function testWillReturnOnConsecutiveCalls()
    {
        $mock = $this->getMockBuilder(stdClass::class)
                     ->setMethods(['foo'])
                     ->getMock();

        $mock->expects($this->any())
             ->method('foo')
             ->willReturnOnConsecutiveCalls(1, 2, 3);

        $this->assertEquals(1, $mock->foo());
        $this->assertEquals(2, $mock->foo());
        $this->assertEquals(3, $mock->foo());
    }

    public function testWillReturnByReference()
    {
        $mock = $this->getMockBuilder(stdClass::class)
                     ->setMethods(['foo'])
                     ->getMock();

        $mock->expects($this->any())
             ->method('foo')
             ->willReturnReference($value);

        $this->assertSame(null, $mock->foo());
        $value = 'foo';
        $this->assertSame('foo', $mock->foo());
        $value = 'bar';
        $this->assertSame('bar', $mock->foo());
    }
}
