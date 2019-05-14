<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\MockObject\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Framework\MockObject\Generator
 *
 * @uses \PHPUnit\Framework\MockObject\InvocationMocker
 * @uses \PHPUnit\Framework\MockObject\Builder\InvocationMocker
 * @uses \PHPUnit\Framework\MockObject\Invocation\ObjectInvocation
 * @uses \PHPUnit\Framework\MockObject\Invocation\StaticInvocation
 * @uses \PHPUnit\Framework\MockObject\Matcher
 * @uses \PHPUnit\Framework\MockObject\Matcher\InvokedRecorder
 * @uses \PHPUnit\Framework\MockObject\Matcher\MethodName
 * @uses \PHPUnit\Framework\MockObject\Stub\ReturnStub
 * @uses \PHPUnit\Framework\MockObject\Matcher\InvokedCount
 */
class GeneratorTest extends TestCase
{
    /**
     * @var Generator
     */
    private $generator;

    protected function setUp(): void
    {
        $this->generator = new Generator;
    }

    public function testGetMockFailsWhenInvalidFunctionNameIsPassedInAsAFunctionToMock(): void
    {
        $this->expectException(\PHPUnit\Framework\MockObject\RuntimeException::class);

        $this->generator->getMock(stdClass::class, [0]);
    }

    public function testGetMockCanCreateNonExistingFunctions(): void
    {
        $mock = $this->generator->getMock(stdClass::class, ['testFunction']);

        $this->assertTrue(\method_exists($mock, 'testFunction'));
    }

    public function testGetMockGeneratorFails(): void
    {
        $this->expectException(\PHPUnit\Framework\MockObject\RuntimeException::class);
        $this->expectExceptionMessage('duplicates: "foo, bar, foo" (duplicate: "foo")');

        $this->generator->getMock(stdClass::class, ['foo', 'bar', 'foo']);
    }

    public function testGetMockBlacklistedMethodNamesPhp7(): void
    {
        $mock = $this->generator->getMock(InterfaceWithSemiReservedMethodName::class);

        $this->assertTrue(\method_exists($mock, 'unset'));
        $this->assertInstanceOf(InterfaceWithSemiReservedMethodName::class, $mock);
    }

    public function testGetMockForAbstractClassDoesNotFailWhenFakingInterfaces(): void
    {
        $mock = $this->generator->getMockForAbstractClass(Countable::class);

        $this->assertTrue(\method_exists($mock, 'count'));
    }

    public function testGetMockForAbstractClassStubbingAbstractClass(): void
    {
        $mock = $this->generator->getMockForAbstractClass(AbstractMockTestClass::class);

        $this->assertTrue(\method_exists($mock, 'doSomething'));
    }

    public function testGetMockForAbstractClassWithNonExistentMethods(): void
    {
        $mock = $this->generator->getMockForAbstractClass(
            AbstractMockTestClass::class,
            [],
            '',
            true,
            true,
            true,
            ['nonexistentMethod']
        );

        $this->assertTrue(\method_exists($mock, 'nonexistentMethod'));
        $this->assertTrue(\method_exists($mock, 'doSomething'));
    }

    public function testGetMockForAbstractClassShouldCreateStubsOnlyForAbstractMethodWhenNoMethodsWereInformed(): void
    {
        $mock = $this->generator->getMockForAbstractClass(AbstractMockTestClass::class);

        $mock->expects($this->any())
             ->method('doSomething')
             ->willReturn('testing');

        $this->assertEquals('testing', $mock->doSomething());
        $this->assertEquals(1, $mock->returnAnything());
    }

    /**
     * @dataProvider getMockForAbstractClassExpectsInvalidArgumentExceptionDataprovider
     */
    public function testGetMockForAbstractClassExpectingInvalidArgumentException($className, $mockClassName): void
    {
        $this->expectException(PHPUnit\Framework\Exception::class);

        $this->generator->getMockForAbstractClass($className, [], $mockClassName);
    }

    public function testGetMockForAbstractClassAbstractClassDoesNotExist(): void
    {
        $this->expectException(\PHPUnit\Framework\MockObject\RuntimeException::class);

        $this->generator->getMockForAbstractClass('Tux');
    }

    public function getMockForAbstractClassExpectsInvalidArgumentExceptionDataprovider(): array
    {
        return [
            'className not a string'     => [[], ''],
            'mockClassName not a string' => [Countable::class, new stdClass],
        ];
    }

    public function testGetMockForTraitWithNonExistentMethodsAndNonAbstractMethods(): void
    {
        $mock = $this->generator->getMockForTrait(
            AbstractTrait::class,
            [],
            '',
            true,
            true,
            true,
            ['nonexistentMethod']
        );

        $this->assertTrue(\method_exists($mock, 'nonexistentMethod'));
        $this->assertTrue(\method_exists($mock, 'doSomething'));
        $this->assertTrue($mock->mockableMethod());
        $this->assertTrue($mock->anotherMockableMethod());
    }

    public function testGetMockForTraitStubbingAbstractMethod(): void
    {
        $mock = $this->generator->getMockForTrait(AbstractTrait::class);

        $this->assertTrue(\method_exists($mock, 'doSomething'));
    }

    public function testGetMockForSingletonWithReflectionSuccess(): void
    {
        $mock = $this->generator->getMock(SingletonClass::class, ['doSomething'], [], '', false);

        $this->assertInstanceOf('SingletonClass', $mock);
    }

    public function testExceptionIsRaisedForMutuallyExclusiveOptions(): void
    {
        $this->expectException(\PHPUnit\Framework\MockObject\RuntimeException::class);

        $this->generator->getMock(stdClass::class, [], [], '', false, true, true, true, true);
    }

    public function testCanImplementInterfacesThatHaveMethodsWithReturnTypes(): void
    {
        $stub = $this->generator->getMock([AnInterfaceWithReturnType::class, AnInterface::class]);

        $this->assertInstanceOf(AnInterfaceWithReturnType::class, $stub);
        $this->assertInstanceOf(AnInterface::class, $stub);
        $this->assertInstanceOf(MockObject::class, $stub);
    }

    public function testCanConfigureMethodsForDoubleOfNonExistentClass(): void
    {
        $className = 'X' . \md5(\microtime());

        $mock = $this->generator->getMock($className, ['someMethod']);

        $this->assertInstanceOf($className, $mock);
    }

    public function testCanInvokeMethodsOfNonExistentClass(): void
    {
        $className = 'X' . \md5(\microtime());

        $mock = $this->generator->getMock($className, ['someMethod']);

        $mock->expects($this->once())->method('someMethod');

        $this->assertNull($mock->someMethod());
    }

    public function testMockingOfExceptionWithThrowable(): void
    {
        $stub = $this->generator->getMock(ExceptionWithThrowable::class);

        $this->assertInstanceOf(ExceptionWithThrowable::class, $stub);
        $this->assertInstanceOf(Exception::class, $stub);
        $this->assertInstanceOf(MockObject::class, $stub);
    }

    public function testMockingOfThrowable(): void
    {
        $stub = $this->generator->getMock(Throwable::class);

        $this->assertInstanceOf(Throwable::class, $stub);
        $this->assertInstanceOf(Exception::class, $stub);
        $this->assertInstanceOf(MockObject::class, $stub);
    }

    public function testVariadicArgumentsArePassedToOriginalMethod()
    {
        /** @var ClassWithVariadicArgumentMethod|MockObject $mock */
        $mock = $this->generator->getMock(
            ClassWithVariadicArgumentMethod::class,
            [],
            [],
            '',
            true,
            false,
            true,
            false,
            true
        );

        $arguments = [1, 'foo', false];
        $this->assertSame($arguments, $mock->foo(...$arguments));
    }

    public function testVariadicArgumentsArePassedToMockedMethod()
    {
        /** @var ClassWithVariadicArgumentMethod|MockObject $mock */
        $mock = $this->createMock(ClassWithVariadicArgumentMethod::class);

        $arguments = [1, 'foo', false];
        $mock->expects($this->once())
            ->method('foo')
            ->with(...$arguments);

        $mock->foo(...$arguments);
    }
}
