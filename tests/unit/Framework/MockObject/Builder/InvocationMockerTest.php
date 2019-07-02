<?php declare(strict_types=1);

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\MockObject\IncompatibleReturnValueException;
use PHPUnit\Framework\MockObject\Stub\MatcherCollection;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Framework\MockObject\Builder\InvocationMocker
 * @small
 */
final class InvocationMockerTest extends TestCase
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
            $this->any()
        );

        $this->expectException(RuntimeException::class);
        $invocationMocker->method('someMethod');
    }

    public function testWillReturnFailsWhenTryingToReturnSingleIncompatibleValue(): void
    {
        $mock = $this->getMockBuilder(ClassWithAllPossibleReturnTypes::class)
            ->getMock();

        $invocationMocker = $mock->method('methodWithBoolReturnTypeDeclaration');

        $this->expectException(IncompatibleReturnValueException::class);
        $this->expectExceptionMessage('Method methodWithBoolReturnTypeDeclaration may not return value of type integer, its return declaration is ": bool"');
        $invocationMocker->willReturn(1);
    }

    public function testWillReturnFailsWhenTryingToReturnIncompatibleValueByConstraint(): void
    {
        $mock = $this->getMockBuilder(ClassWithAllPossibleReturnTypes::class)
            ->getMock();

        $invocationMocker = $mock->method(new \PHPUnit\Framework\Constraint\IsEqual('methodWithBoolReturnTypeDeclaration'));

        $this->expectException(IncompatibleReturnValueException::class);
        $this->expectExceptionMessage('Method methodWithBoolReturnTypeDeclaration may not return value of type integer, its return declaration is ": bool"');
        $invocationMocker->willReturn(1);
    }

    public function testWillReturnFailsWhenTryingToReturnAtLeastOneIncompatibleValue(): void
    {
        $mock = $this->getMockBuilder(ClassWithAllPossibleReturnTypes::class)
            ->getMock();

        $invocationMocker = $mock->method('methodWithBoolReturnTypeDeclaration');

        $this->expectException(IncompatibleReturnValueException::class);
        $this->expectExceptionMessage('Method methodWithBoolReturnTypeDeclaration may not return value of type integer, its return declaration is ": bool"');
        $invocationMocker->willReturn(true, 1);
    }

    public function testWillReturnFailsWhenTryingToReturnSingleIncompatibleClass(): void
    {
        $mock = $this->getMockBuilder(ClassWithAllPossibleReturnTypes::class)
            ->getMock();

        $invocationMocker = $mock->method('methodWithClassReturnTypeDeclaration');

        $this->expectException(IncompatibleReturnValueException::class);
        $this->expectExceptionMessage('Method methodWithClassReturnTypeDeclaration may not return value of type Foo, its return declaration is ": stdClass"');
        $invocationMocker->willReturn(new Foo());
    }

    public function testWillReturnAllowsMatchersForMultipleMethodsWithDifferentReturnTypes(): void
    {
        /** @var ClassWithAllPossibleReturnTypes|\PHPUnit\Framework\MockObject\MockObject $mock */
        $mock = $this->getMockBuilder(ClassWithAllPossibleReturnTypes::class)
            ->getMock();

        $invocationMocker = $mock->method(new \PHPUnit\Framework\Constraint\IsAnything());
        $invocationMocker->willReturn(true, 1);

        $this->assertEquals(true, $mock->methodWithBoolReturnTypeDeclaration());
        $this->assertEquals(1, $mock->methodWithIntReturnTypeDeclaration());
    }

    public function testWillReturnValidType(): void
    {
        $mock = $this->getMockBuilder(ClassWithAllPossibleReturnTypes::class)
            ->getMock();

        $mock->expects($this->any())
            ->method('methodWithBoolReturnTypeDeclaration')
            ->willReturn(true);

        $this->assertEquals(true, $mock->methodWithBoolReturnTypeDeclaration());
    }

    public function testWillReturnValidTypeForLowercaseCall(): void
    {
        $mock = $this->getMockBuilder(ClassWithAllPossibleReturnTypes::class)
            ->getMock();

        $mock->expects($this->any())
            ->method('methodWithBoolReturnTypeDeclaration')
            ->willReturn(true);

        $this->assertEquals(true, $mock->methodwithboolreturntypedeclaration());
    }

    public function testWillReturnValidTypeForLowercaseMethod(): void
    {
        $mock = $this->getMockBuilder(ClassWithAllPossibleReturnTypes::class)
            ->getMock();

        $mock->expects($this->any())
            ->method('methodwithboolreturntypedeclaration')
            ->willReturn(true);

        $this->assertEquals(true, $mock->methodWithBoolReturnTypeDeclaration());
    }

    /**
     * @see https://github.com/sebastianbergmann/phpunit/issues/3602
     */
    public function testWillReturnFailsWhenTryingToReturnValueFromVoidMethod(): void
    {
        /** @var ClassWithAllPossibleReturnTypes|\PHPUnit\Framework\MockObject\MockObject $out */
        $out    = $this->createMock(ClassWithAllPossibleReturnTypes::class);
        $method = $out->method('methodWithVoidReturnTypeDeclaration');

        $this->expectException(IncompatibleReturnValueException::class);
        $this->expectExceptionMessage('Method methodWithVoidReturnTypeDeclaration may not return value of type boolean, its return declaration is ": void"');
        $method->willReturn(true);
    }
}
