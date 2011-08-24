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

    public function testGetMockCanCreateNonExistingFunctions() {
        $mock = PHPUnit_Framework_MockObject_Generator::getMock('stdClass', array('testFunction'));
        $this->assertTrue(method_exists($mock, 'testFunction'));
    }
}
