<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\MockObject\Stub\MatcherCollection;
use PHPUnit\Framework\TestCase;

class InvocationMockerTest extends TestCase
{
    public function testWillReturnWithOneValue(): void
    {
        $mock = $this->getMockBuilder(stdClass::class)
                     ->setMethods(['foo'])
                     ->getMock();

        $mock->expects($this->any())
             ->method('foo')
             ->willReturn(1);

        $this->assertEquals(1, $mock->foo());
    }

    public function testWillReturnWithMultipleValues(): void
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

    public function testWillReturnOnConsecutiveCalls(): void
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

    public function testWillReturnByReference(): void
    {
        $mock = $this->getMockBuilder(stdClass::class)
                     ->setMethods(['foo'])
                     ->getMock();

        $mock->expects($this->any())
             ->method('foo')
             ->willReturnReference($value);

        $this->assertNull($mock->foo());
        $value = 'foo';
        $this->assertSame('foo', $mock->foo());
        $value = 'bar';
        $this->assertSame('bar', $mock->foo());
    }

    public function testWillFailWhenTryingToPerformExpectationUnconfigurableMethod(): void
    {
        /** @var MatcherCollection|\PHPUnit\Framework\MockObject\MockObject $matcherCollection */
        $matcherCollection = $this->createMock(MatcherCollection::class);
        $invocationMocker  = new \PHPUnit\Framework\MockObject\Builder\InvocationMocker(
            $matcherCollection,
            $this->any(),
            []
        );

        $this->expectException(RuntimeException::class);
        $invocationMocker->method('someMethod');
    }
}
