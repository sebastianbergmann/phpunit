<?php
class Framework_MockObject_GeneratorTest extends PHPUnit_Framework_TestCase
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
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testGetMockFailsWhenInvalidFunctionNameIsPassedInAsAFunctionToMock()
    {
        $this->generator->getMock('StdClass', [0]);
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMock
     */
    public function testGetMockCanCreateNonExistingFunctions()
    {
        $mock = $this->generator->getMock('StdClass', ['testFunction']);
        $this->assertTrue(method_exists($mock, 'testFunction'));
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMock
     * @expectedException PHPUnit_Framework_MockObject_RuntimeException
     * @expectedExceptionMessage duplicates: "foo, foo"
     */
    public function testGetMockGeneratorFails()
    {
        $mock = $this->generator->getMock('StdClass', ['foo', 'foo']);
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMock
     * @covers PHPUnit_Framework_MockObject_Generator::isMethodNameBlacklisted
     */
    public function testGetMockBlacklistedMethodNamesPhp7()
    {
        if (PHP_MAJOR_VERSION < 7) {
            $this->markTestSkipped('PHP >= 7.0.0 required');

            return;
        }

        // Probably, this should be moved to tests/autoload.php
        require_once __DIR__ . '/_fixture/InterfaceWithSemiReservedMethodName.php';

        $mock = $this->generator->getMock('InterfaceWithSemiReservedMethodName');

        $this->assertTrue(method_exists($mock, 'unset'));
        $this->assertInstanceOf('InterfaceWithSemiReservedMethodName', $mock);
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass
     */
    public function testGetMockForAbstractClassDoesNotFailWhenFakingInterfaces()
    {
        $mock = $this->generator->getMockForAbstractClass('Countable');
        $this->assertTrue(method_exists($mock, 'count'));
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass
     */
    public function testGetMockForAbstractClassStubbingAbstractClass()
    {
        $mock = $this->generator->getMockForAbstractClass('AbstractMockTestClass');
        $this->assertTrue(method_exists($mock, 'doSomething'));
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass
     */
    public function testGetMockForAbstractClassWithNonExistentMethods()
    {
        $mock = $this->generator->getMockForAbstractClass(
            'AbstractMockTestClass',
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
        $mock = $this->generator->getMockForAbstractClass('AbstractMockTestClass');

        $mock->expects($this->any())
             ->method('doSomething')
             ->willReturn('testing');

        $this->assertEquals('testing', $mock->doSomething());
        $this->assertEquals(1, $mock->returnAnything());
    }

    /**
     * @dataProvider getMockForAbstractClassExpectsInvalidArgumentExceptionDataprovider
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testGetMockForAbstractClassExpectingInvalidArgumentException($className, $mockClassName)
    {
        $mock = $this->generator->getMockForAbstractClass($className, [], $mockClassName);
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass
     * @expectedException PHPUnit_Framework_MockObject_RuntimeException
     */
    public function testGetMockForAbstractClassAbstractClassDoesNotExist()
    {
        $mock = $this->generator->getMockForAbstractClass('Tux');
    }

    /**
     * Dataprovider for test "testGetMockForAbstractClassExpectingInvalidArgumentException"
     */
    public static function getMockForAbstractClassExpectsInvalidArgumentExceptionDataprovider()
    {
        return [
            'className not a string'     => [[], ''],
            'mockClassName not a string' => ['Countable', new StdClass],
        ];
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForTrait
     */
    public function testGetMockForTraitWithNonExistentMethodsAndNonAbstractMethods()
    {
        $mock = $this->generator->getMockForTrait(
            'AbstractTrait',
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
        $mock = $this->generator->getMockForTrait('AbstractTrait');
        $this->assertTrue(method_exists($mock, 'doSomething'));
    }

    public function testGetMockForSingletonWithReflectionSuccess()
    {
        // Probably, this should be moved to tests/autoload.php
        require_once __DIR__ . '/_fixture/SingletonClass.php';

        $mock = $this->generator->getMock('SingletonClass', ['doSomething'], [], '', false);
        $this->assertInstanceOf('SingletonClass', $mock);
    }
}
