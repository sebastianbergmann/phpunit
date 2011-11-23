<?php
class Framework_MockObject_GeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMock
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testGetMockFailsWhenInvalidFunctionNameIsPassedInAsAFunctionToMock()
    {
        PHPUnit_Framework_MockObject_Generator::getMock('stdClass', array(0));
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMock
     */
    public function testGetMockCanCreateNonExistingFunctions()
    {
        $mock = PHPUnit_Framework_MockObject_Generator::getMock('stdClass', array('testFunction'));
        $this->assertTrue(method_exists($mock, 'testFunction'));
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass
     * @covers PHPUnit_Framework_MockObject_Generator::abstractClassExists
     */
    public function testGetMockForAbstractClassDoesNotFailWhenFakingInterfaces()
    {
        $mock = PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass('Countable');
        $this->assertTrue(method_exists($mock, 'count'));
    }

    /**
     * @dataProvider getMockForAbstractClassExpectsInvalidArgumentExceptionDataprovider
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass
     * @expectedException InvalidArgumentException
     */
    public function testGetMockForAbstractClassExpectingInvalidArgumentException($className, $mockClassName)
    {
        $mock = PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass($className, array(), $mockClassName);
    }

    /**
     * @covers PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass
     * @covers PHPUnit_Framework_MockObject_Generator::abstractClassExists
     * @expectedException PHPUnit_Framework_Exception
     */
    public function testGetMockForAbstractClassAnstractClassDoesNotExist()
    {
        $mock = PHPUnit_Framework_MockObject_Generator::getMockForAbstractClass('Tux');
    }

    /*************************************************************************/
    /* Dataprovider / Callbacks                                              */
    /*************************************************************************/

    public static function getMockForAbstractClassExpectsInvalidArgumentExceptionDataprovider()
    {
        return array(
            'className not a string' => array(array(), ''),
            'mockClassName not a string' => array('Countable', new stdClass()),
        );
    }
}
