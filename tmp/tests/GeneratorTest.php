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

class Framework_MockObject_GeneratorTest extends TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_Generator
     */
    protected $generator;

    protected function setUp()
    {
        $this->generator = new PHPUnit_Framework_MockObject_Generator;
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMock
     * @expectedException PHPUnit_Framework_MockObject_RuntimeException
     */
    public function testGetMockFailsWhenInvalidFunctionNameIsPassedInAsAFunctionToMock()
    {
        $this->generator->getMock(stdClass::class, [0]);
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMock
     */
    public function testGetMockCanCreateNonExistingFunctions()
    {
        $mock = $this->generator->getMock(stdClass::class, ['testFunction']);

        $this->assertTrue(method_exists($mock, 'testFunction'));
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMock
     * @expectedException PHPUnit_Framework_MockObject_RuntimeException
     * @expectedExceptionMessage duplicates: "foo, bar, foo" (duplicate: "foo")
     */
    public function testGetMockGeneratorFails()
    {
        $this->generator->getMock(stdClass::class, ['foo', 'bar', 'foo']);
    }

    /**
     * @covers   PHPUnit_Framework_MockObject_Generator::getMock
     * @covers   PHPUnit_Framework_MockObject_Generator::isMethodNameBlacklisted
     * @requires PHP 7
     */
    public function testGetMockBlacklistedMethodNamesPhp7()
    {
        $mock = $this->generator->getMock(InterfaceWithSemiReservedMethodName::class);

        $this->assertTrue(method_exists($mock, 'unset'));
        $this->assertInstanceOf(InterfaceWithSemiReservedMethodName::class, $mock);
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass
     */
    public function testGetMockForAbstractClassDoesNotFailWhenFakingInterfaces()
    {
        $mock = $this->generator->getMockForAbstractClass(Countable::class);

        $this->assertTrue(method_exists($mock, 'count'));
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass
     */
    public function testGetMockForAbstractClassStubbingAbstractClass()
    {
        $mock = $this->generator->getMockForAbstractClass(AbstractMockTestClass::class);

        $this->assertTrue(method_exists($mock, 'doSomething'));
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass
     */
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

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass
     */
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
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass
     * @expectedException PHPUnit\Framework\Exception
     */
    public function testGetMockForAbstractClassExpectingInvalidArgumentException($className, $mockClassName)
    {
        $this->generator->getMockForAbstractClass($className, [], $mockClassName);
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass
     * @expectedException PHPUnit_Framework_MockObject_RuntimeException
     */
    public function testGetMockForAbstractClassAbstractClassDoesNotExist()
    {
        $this->generator->getMockForAbstractClass('Tux');
    }

    public function getMockForAbstractClassExpectsInvalidArgumentExceptionDataprovider()
    {
        return [
            'className not a string'     => [[], ''],
            'mockClassName not a string' => [Countable::class, new stdClass],
        ];
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForTrait
     */
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

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForTrait
     */
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

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMock
     * @expectedException PHPUnit_Framework_MockObject_RuntimeException
     */
    public function testExceptionIsRaisedForMutuallyExclusiveOptions()
    {
        $this->generator->getMock(stdClass::class, [], [], '', false, true, true, true, true);
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMock
     *
     * @requires PHP 7
     */
    public function testCanImplementInterfacesThatHaveMethodsWithReturnTypes()
    {
        $stub = $this->generator->getMock([AnInterfaceWithReturnType::class, AnInterface::class]);

        $this->assertInstanceOf(AnInterfaceWithReturnType::class, $stub);
        $this->assertInstanceOf(AnInterface::class, $stub);
        $this->assertInstanceOf(PHPUnit_Framework_MockObject_MockObject::class, $stub);
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMock
     *
     * @ticket https://github.com/sebastianbergmann/phpunit-mock-objects/issues/322
     */
    public function testCanConfigureMethodsForDoubleOfNonExistentClass()
    {
        $className = 'X' . md5(microtime());

        $mock = $this->generator->getMock($className, ['someMethod']);

        $this->assertInstanceOf($className, $mock);
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMock
     */
    public function testCanInvokeMethodsOfNonExistentClass()
    {
        $className = 'X' . md5(microtime());

        $mock = $this->generator->getMock($className, ['someMethod']);

        $mock->expects($this->once())->method('someMethod');

        $this->assertNull($mock->someMethod());
    }
}
