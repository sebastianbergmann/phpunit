<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\TestFixture;

use PHPUnit\Framework\TestCase;

interface GreeterForPartialFunctionApplication
{
    public function greet(string $name, string $punctuation): string;
}

final class TestDoubleAndPartialFunctionApplicationTest extends TestCase
{
    public function testPartiallyAppliedDoubledMethodIsInvokedWithBoundAndPlaceholderArguments(): void
    {
        $double = $this->createMock(GreeterForPartialFunctionApplication::class);

        $double->expects($this->once())
            ->method('greet')
            ->with('world', '!')
            ->willReturn('hello world!');

        $partial = $double->greet(?, '!');

        $this->assertSame('hello world!', $partial('world'));
    }

    public function testCreatingPartialFromDoubledMethodDoesNotCountAsInvocation(): void
    {
        $double = $this->createMock(GreeterForPartialFunctionApplication::class);

        $double->expects($this->never())->method('greet');

        $double->greet(?, '!');
    }

    public function testPartialWithBoundArgumentAndVariadicPlaceholderInvokesDoubledMethod(): void
    {
        $double = $this->createStub(GreeterForPartialFunctionApplication::class);

        $double->method('greet')->willReturn('hello world!');

        $partial = $double->greet('world', ...);

        $this->assertSame('hello world!', $partial('!'));
    }
}
