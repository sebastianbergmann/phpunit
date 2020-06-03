<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;

class InvocationHandlerTest extends TestCase
{
    public function testExceptionThrownIn__ToStringIsDeferred(): void
    {
        $mock = $this->createMock(\StringableClass::class);
        $mock->method('__toString')
            ->willThrowException(new \RuntimeException('planned error'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('planned error');
        $mock->__toString();
    }

    public function testSingleMatcherIsHandled(): void
    {
        $mock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['foo'])
            ->getMock();

        $mock->expects($this->once())
            ->method('foo')
            ->willReturn('result');

        $this->assertSame('result', $mock->foo());
    }

    public function testNonUniqueMatchThrowsException(): void
    {
        $mock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['foo'])
            ->getMock();

        $mock->expects($this->any())
            ->method($this->stringStartsWith('foo'))
            ->willReturn('result');

        $mock->expects($this->any())
            ->method('foo')
            ->with('bar')
            ->willReturn('result');

        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Non unique mocked method invocation: stdClass::foo');

        $mock->foo();
    }
}
