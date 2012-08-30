<?php
class Framework_MockObject_GeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMock
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testGetMockFailsWhenInvalidFunctionNameIsPassedInAsAFunctionToMock()
    {
        PHPUnit_Framework_MockObject_Generator::getMock('StdClass', array(0));
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMock
     */
    public function testGetMockCanCreateNonExistingFunctions()
    {
        $mock = PHPUnit_Framework_MockObject_Generator::getMock('StdClass', array('testFunction'));
        $this->assertTrue(method_exists($mock, 'testFunction'));
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMock
     * @expectedException PHPUnit_Framework_Exception
     * @expectedExceptionMessage duplicates: "foo, foo"
     */
    public function testGetMockGeneratorFails()
    {
        $mock = PHPUnit_Framework_MockObject_Generator::getMock('StdClass', array('foo', 'foo'));
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getObject
     */
    public function testMockObjectHasUniqueIdSoThatTwoMockObjectsOfTheSameClassAreNotEqual()
    {
        $mock1 = PHPUnit_Framework_MockObject_Generator::getMock('stdClass');
        $mock2 = PHPUnit_Framework_MockObject_Generator::getMock('stdClass');
        $this->assertNotEquals($mock1, $mock2);
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass
     */
    public function testGetMockForAbstractClassDoesNotFailWhenFakingInterfaces()
    {
        $mock = PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass('Countable');
        $this->assertTrue(method_exists($mock, 'count'));
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass
     */
    public function testGetMockForAbstractClassStubbingAbstractClass()
    {
        $mock = PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass('AbstractMockTestClass');
        $this->assertTrue(method_exists($mock, 'doSomething'));
    }

    /**
     * @dataProvider getMockForAbstractClassExpectsInvalidArgumentExceptionDataprovider
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testGetMockForAbstractClassExpectingInvalidArgumentException($className, $mockClassName)
    {
        $mock = PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass($className, array(), $mockClassName);
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testGetMockForAbstractClassAnstractClassDoesNotExist()
    {
        $mock = PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass('Tux');
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
}
