<?php
/*
 * This file is part of the phpunit-mock-objects package.
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

    protected function setUp()
    {
        $this->generator = new Generator;
    }

    public function testGetMockFailsWhenInvalidFunctionNameIsPassedInAsAFunctionToMock()
    {
        $this->expectException(\PHPUnit\Framework\MockObject\RuntimeException::class);

        $this->generator->getMock(stdClass::class, [0]);
    }

    public function testGetMockCanCreateNonExistingFunctions()
    {
        $mock = $this->generator->getMock(stdClass::class, ['testFunction']);

        $this->assertTrue(method_exists($mock, 'testFunction'));
    }

    public function testGetMockGeneratorFails()
    {
        $this->expectException(\PHPUnit\Framework\MockObject\RuntimeException::class);
        $this->expectExceptionMessage('duplicates: "foo, bar, foo" (duplicate: "foo")');

        $this->generator->getMock(stdClass::class, ['foo', 'bar', 'foo']);
    }

    public function testGetMockBlacklistedMethodNamesPhp7()
    {
        $mock = $this->generator->getMock(InterfaceWithSemiReservedMethodName::class);

        $this->assertTrue(method_exists($mock, 'unset'));
        $this->assertInstanceOf(InterfaceWithSemiReservedMethodName::class, $mock);
    }

    public function testGetMockForAbstractClassDoesNotFailWhenFakingInterfaces()
    {
        $mock = $this->generator->getMockForAbstractClass(Countable::class);

        $this->assertTrue(method_exists($mock, 'count'));
    }

    public function testGetMockForAbstractClassStubbingAbstractClass()
    {
        $mock = $this->generator->getMockForAbstractClass(AbstractMockTestClass::class);

        $this->assertTrue(method_exists($mock, 'doSomething'));
    }

    public function testGetMockForAbstractClassWithNonExistentMethods()
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

        $this->assertTrue(method_exists($mock, 'nonexistentMethod'));
        $this->assertTrue(method_exists($mock, 'doSomething'));
    }

    public function testGetMockForAbstractClassShouldCreateStubsOnlyForAbstractMethodWhenNoMethodsWereInformed()
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
    public function testGetMockForAbstractClassExpectingInvalidArgumentException($className, $mockClassName)
    {
        $this->expectException(PHPUnit\Framework\Exception::class);

        $this->generator->getMockForAbstractClass($className, [], $mockClassName);
    }

    public function testGetMockForAbstractClassAbstractClassDoesNotExist()
    {
        $this->expectException(\PHPUnit\Framework\MockObject\RuntimeException::class);

        $this->generator->getMockForAbstractClass('Tux');
    }

    public function getMockForAbstractClassExpectsInvalidArgumentExceptionDataprovider()
    {
        return [
            'className not a string'     => [[], ''],
            'mockClassName not a string' => [Countable::class, new stdClass],
        ];
    }

    public function testGetMockForTraitWithNonExistentMethodsAndNonAbstractMethods()
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

        $this->assertTrue(method_exists($mock, 'nonexistentMethod'));
        $this->assertTrue(method_exists($mock, 'doSomething'));
        $this->assertTrue($mock->mockableMethod());
        $this->assertTrue($mock->anotherMockableMethod());
    }

    public function testGetMockForTraitStubbingAbstractMethod()
    {
        $mock = $this->generator->getMockForTrait(AbstractTrait::class);

        $this->assertTrue(method_exists($mock, 'doSomething'));
    }

    public function testGetMockForSingletonWithReflectionSuccess()
    {
        $mock = $this->generator->getMock(SingletonClass::class, ['doSomething'], [], '', false);

        $this->assertInstanceOf('SingletonClass', $mock);
    }

    public function testExceptionIsRaisedForMutuallyExclusiveOptions()
    {
        $this->expectException(\PHPUnit\Framework\MockObject\RuntimeException::class);

        $this->generator->getMock(stdClass::class, [], [], '', false, true, true, true, true);
    }

    public function testCanImplementInterfacesThatHaveMethodsWithReturnTypes()
    {
        $stub = $this->generator->getMock([AnInterfaceWithReturnType::class, AnInterface::class]);

        $this->assertInstanceOf(AnInterfaceWithReturnType::class, $stub);
        $this->assertInstanceOf(AnInterface::class, $stub);
        $this->assertInstanceOf(MockObject::class, $stub);
    }

    public function testCanConfigureMethodsForDoubleOfNonExistentClass()
    {
        $className = 'X' . md5(microtime());

        $mock = $this->generator->getMock($className, ['someMethod']);

        $this->assertInstanceOf($className, $mock);
    }

    public function testCanInvokeMethodsOfNonExistentClass()
    {
        $className = 'X' . md5(microtime());

        $mock = $this->generator->getMock($className, ['someMethod']);

        $mock->expects($this->once())->method('someMethod');

        $this->assertNull($mock->someMethod());
    }

    public function testMockingOfThrowable()
    {
        $stub = $this->generator->getMock(ExceptionWithThrowable::class);

        $this->assertInstanceOf(ExceptionWithThrowable::class, $stub);
        $this->assertInstanceOf(Exception::class, $stub);
        $this->assertInstanceOf(MockObject::class, $stub);
    }
}
