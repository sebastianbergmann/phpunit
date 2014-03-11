<?php
class Framework_MockObject_GeneratorTest extends PHPUnit_Framework_TestCase
{
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
        $this->generator->getMock('StdClass', array(0));
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMock
     */
    public function testGetMockCanCreateNonExistingFunctions()
    {
        $mock = $this->generator->getMock('StdClass', array('testFunction'));
        $this->assertTrue(method_exists($mock, 'testFunction'));
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMock
     * @expectedException PHPUnit_Framework_Exception
     * @expectedExceptionMessage duplicates: "foo, foo"
     */
    public function testGetMockGeneratorFails()
    {
        $mock = $this->generator->getMock('StdClass', array('foo', 'foo'));
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
            'AbstractMockTestClass', array(), '',  true,
            true, true, array('nonexistentMethod')
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
        $mock = $this->generator->getMockForAbstractClass($className, array(), $mockClassName);
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testGetMockForAbstractClassAnstractClassDoesNotExist()
    {
        $mock = $this->generator->getMockForAbstractClass('Tux');
    }

    /**
     * Dataprovider for test "testGetMockForAbstractClassExpectingInvalidArgumentException"
     */
    public static function getMockForAbstractClassExpectsInvalidArgumentExceptionDataprovider()
    {
        return array(
            'className not a string' => array(array(), ''),
            'mockClassName not a string' => array('Countable', new StdClass),
        );
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForTrait
     * @requires PHP 5.4.0
     */
    public function testGetMockForTraitWithNonExistentMethodsAndNonAbstractMethods()
    {
        $mock = $this->generator->getMockForTrait(
            'AbstractTrait', array(), '',  true,
            true, true, array('nonexistentMethod')
        );

        $this->assertTrue(method_exists($mock, 'nonexistentMethod'));
        $this->assertTrue(method_exists($mock, 'doSomething'));
        $this->assertTrue($mock->mockableMethod());
        $this->assertTrue($mock->anotherMockableMethod());
    }

    /**
     * @dataProvider getMockForTraitExpectsInvalidArgumentExceptionDataprovider
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForTrait
     * @requires PHP 5.4.0
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testGetMockForTraitExpectingInvalidArgumentException($traitName, $mockClassName)
    {
        $mock = $this->generator->getMockForTrait($traitName, array(), $mockClassName);
    }

    /**
     * @covers   PHPUnit_Framework_MockObject_Generator::getMockForTrait
     * @requires PHP 5.4.0
     */
    public function testGetMockForTraitStubbingAbstractMethod()
    {
        $mock = $this->generator->getMockForTrait('AbstractTrait');
        $this->assertTrue(method_exists($mock, 'doSomething'));
    }

    /**
     * Dataprovider for test "testGetMockForTraitExpectingInvalidArgumentException"
     */
    public static function getMockForTraitExpectsInvalidArgumentExceptionDataprovider()
    {
        return array(
            'traitName not a string' => array(array(), ''),
            'mockClassName not a string' => array('AbstractTrait', new StdClass),
            'trait does not exist' => array('AbstractTraitDoesNotExist', 'TraitTest')
        );
    }
}

