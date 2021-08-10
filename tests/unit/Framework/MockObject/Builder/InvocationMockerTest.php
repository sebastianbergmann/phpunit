<?php declare(strict_types=1);

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use PHPUnit\Framework\Constraint\IsEqual;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\IncompatibleReturnValueException;
use PHPUnit\Framework\MockObject\InvocationHandler;
use PHPUnit\Framework\MockObject\Matcher;
use PHPUnit\Framework\MockObject\MethodCannotBeConfiguredException;
use PHPUnit\Framework\MockObject\Stub\ReturnSelf;
use PHPUnit\Framework\MockObject\Stub\ReturnStub;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\ClassWithAllPossibleReturnTypes;
use PHPUnit\TestFixture\MockObject\ClassWithImplicitProtocol;

/**
 * @covers \PHPUnit\Framework\MockObject\Builder\InvocationMocker
 * @small
 */
final class InvocationMockerTest extends TestCase
{
    public function testWillReturnWithOneValue(): void
    {
        $mock = $this->getMockBuilder(stdClass::class)
            ->addMethods(['foo'])
            ->getMock();

        $mock->method('foo')
             ->willReturn(1);

        $this->assertEquals(1, $mock->foo());
    }

    public function testWillReturnWithMultipleValues(): void
    {
        $mock = $this->getMockBuilder(stdClass::class)
            ->addMethods(['foo'])
            ->getMock();

        $mock->method('foo')
             ->willReturn(1, 2, 3);

        $this->assertEquals(1, $mock->foo());
        $this->assertEquals(2, $mock->foo());
        $this->assertEquals(3, $mock->foo());
    }

    public function testWillReturnOnConsecutiveCalls(): void
    {
        $mock = $this->getMockBuilder(stdClass::class)
            ->addMethods(['foo'])
            ->getMock();

        $mock->method('foo')
             ->willReturnOnConsecutiveCalls(1, 2, 3);

        $this->assertEquals(1, $mock->foo());
        $this->assertEquals(2, $mock->foo());
        $this->assertEquals(3, $mock->foo());
    }

    public function testWillReturnByReference(): void
    {
        $mock = $this->getMockBuilder(stdClass::class)
            ->addMethods(['foo'])
            ->getMock();

        $mock->method('foo')
             ->willReturnReference($value);

        $this->assertNull($mock->foo());
        $value = 'foo';
        $this->assertSame('foo', $mock->foo());
        $value = 'bar';
        $this->assertSame('bar', $mock->foo());
    }

    public function testWillFailWhenTryingToPerformExpectationUnconfigurableMethod(): void
    {
        $matcherCollection = new InvocationHandler([], false);

        $invocationMocker = new InvocationMocker(
            $matcherCollection,
            new Matcher($this->any())
        );

        $this->expectException(MethodCannotBeConfiguredException::class);

        $invocationMocker->method('someMethod');
    }

    public function testWillReturnFailsWhenTryingToReturnSingleIncompatibleValue(): void
    {
        $mock = $this->getMockBuilder(ClassWithAllPossibleReturnTypes::class)
            ->getMock();

        $invocationMocker = $mock->method('methodWithBoolReturnTypeDeclaration');

        $this->expectException(IncompatibleReturnValueException::class);
        $this->expectExceptionMessage('Method methodWithBoolReturnTypeDeclaration may not return value of type integer, its declared return type is "bool"');
        $invocationMocker->willReturn(1);
    }

    public function testWillReturnFailsWhenTryingToReturnIncompatibleValueByConstraint(): void
    {
        $mock = $this->getMockBuilder(ClassWithAllPossibleReturnTypes::class)
            ->getMock();

        $invocationMocker = $mock->method(new IsEqual('methodWithBoolReturnTypeDeclaration'));

        $this->expectException(IncompatibleReturnValueException::class);
        $this->expectExceptionMessage('Method methodWithBoolReturnTypeDeclaration may not return value of type integer, its declared return type is "bool"');
        $invocationMocker->willReturn(1);
    }

    public function testWillReturnFailsWhenTryingToReturnAtLeastOneIncompatibleValue(): void
    {
        $mock = $this->getMockBuilder(ClassWithAllPossibleReturnTypes::class)
            ->getMock();

        $invocationMocker = $mock->method('methodWithBoolReturnTypeDeclaration');

        $this->expectException(IncompatibleReturnValueException::class);
        $this->expectExceptionMessage('Method methodWithBoolReturnTypeDeclaration may not return value of type integer, its declared return type is "bool"');
        $invocationMocker->willReturn(true, 1);
    }

    public function testWillReturnFailsWhenTryingToReturnSingleIncompatibleClass(): void
    {
        $mock = $this->getMockBuilder(ClassWithAllPossibleReturnTypes::class)
            ->getMock();

        $invocationMocker = $mock->method('methodWithClassReturnTypeDeclaration');

        $this->expectException(IncompatibleReturnValueException::class);
        $this->expectExceptionMessage('Method methodWithClassReturnTypeDeclaration may not return value of type Foo, its declared return type is "stdClass"');
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

        $mock->method('methodWithBoolReturnTypeDeclaration')
             ->willReturn(true);

        $this->assertEquals(true, $mock->methodWithBoolReturnTypeDeclaration());
    }

    public function testWillReturnValidTypeForLowercaseCall(): void
    {
        $mock = $this->getMockBuilder(ClassWithAllPossibleReturnTypes::class)
            ->getMock();

        $mock->method('methodWithBoolReturnTypeDeclaration')
             ->willReturn(true);

        $this->assertEquals(true, $mock->methodwithboolreturntypedeclaration());
    }

    public function testWillReturnValidTypeForLowercaseMethod(): void
    {
        $mock = $this->getMockBuilder(ClassWithAllPossibleReturnTypes::class)
            ->getMock();

        $mock->method('methodwithboolreturntypedeclaration')
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
        $this->expectExceptionMessage('Method methodWithVoidReturnTypeDeclaration may not return value of type boolean, its declared return type is "void"');
        $method->willReturn(true);
    }

    public function testExpectationsAreEnabledByPreviousMethodCallWhenChainedWithAfter(): void
    {
        $mock = $this->createMock(ClassWithImplicitProtocol::class);

        $mock->expects($this->once())
            ->method('firstCall')
            ->id($fristCallId = 'first-call-id');

        $mock->expects($this->once())
            ->method('secondCall')
            ->after($fristCallId);

        $mock->firstCall();
        $mock->secondCall();
    }

    public function testExpectationsAreNotTriggeredUntilPreviousMethodWasCalled(): void
    {
        $mock = $this->createMock(ClassWithImplicitProtocol::class);

        $mock->expects($this->once())
            ->method('firstCall')
            ->id($firstCallId = 'first-call-id');

        $mock->expects($this->once())
            ->method('secondCall')
            ->after($firstCallId);

        $mock->secondCall();
        $mock->firstCall();
        $mock->secondCall();
    }

    public function testWillReturnAlreadyInstantiatedStubs(): void
    {
        $mock = $this->getMockBuilder(stdClass::class)
            ->addMethods(['foo', 'bar'])
            ->getMock();

        $mock->method('foo')
             ->willReturn(new ReturnStub('foo'));

        $mock->method('bar')
             ->willReturn(new ReturnSelf());

        $this->assertSame('foo', $mock->foo());
        $this->assertSame($mock, $mock->bar());
    }
}
