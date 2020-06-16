<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;

/**
 * @small
 */
final class ReturnValueMapTest extends TestCase
{
    public function testReturnsTheFirstMatchFound(): void
    {
        $map = [
            ['a', 'b', 'c', 'd'],
            ['a', 'b', 'c', 'e'],
            ['e', 'f', 'g', 'h'],
        ];
        $mock = $this->getMockBuilder(AnInterface::class)
            ->getMock();
        $mock->expects($this->any())
            ->method('doSomething')
            ->will($this->returnValueMap($map));
        $this->assertEquals('d', $mock->doSomething('a', 'b', 'c'));
    }

    public function testAcceptsFrameworkMatchers(): void
    {
        $map = [
            [$this->lessThan(2), 1],
            [$this->greaterThanOrEqual(2), 2],
        ];
        $mock = $this->getMockBuilder(AnInterface::class)
            ->getMock();
        $mock->expects($this->any())
            ->method('doSomething')
            ->will($this->returnValueMap($map));
        $this->assertEquals(1, $mock->doSomething(0));
        $this->assertEquals(2, $mock->doSomething(100));
    }

    public function testAcceptsStubForReturnValue(): void
    {
        $callback = $this->returnCallback(
            function ($arg) {
                return $arg + 1;
            }
        );
        $map = [
            [$this->lessThan(2), 1],
            [$this->greaterThanOrEqual(2), $callback],
        ];
        $mock = $this->getMockBuilder(AnInterface::class)
            ->getMock();
        $mock->expects($this->any())
            ->method('doSomething')
            ->will($this->returnValueMap($map));
        $this->assertEquals(3, $mock->doSomething(2));
    }

    public function testReturnsNullIfNoMatchFound(): void
    {
        $map = [
            ['a', 'b', 'c', 'd'],
        ];
        $mock = $this->getMockBuilder(AnInterface::class)
            ->getMock();
        $mock->expects($this->any())
            ->method('doSomething')
            ->will($this->returnValueMap($map));
        $this->assertEquals(null, $mock->doSomething('foo', 'bar'));
    }
}
