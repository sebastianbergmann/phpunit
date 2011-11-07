<?php

require_once 'PHPUnit/Framework/TestCase.php';


class Framework_MockObject_Invocation_ObjectTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorRequiresClassAndMethodAndParametersAndObject()
    {
        new PHPUnit_Framework_MockObject_Invocation_Object(
            'FooClass',
            'FooMethod',
            array('an_argument'),
            new stdClass());
    }

    public function testAllowToGetClassNameSetInConstructor()
    {
        $invocation = new PHPUnit_Framework_MockObject_Invocation_Object(
            'FooClass',
            'FooMethod',
            array('an_argument'),
            new stdClass());

        $this->assertSame('FooClass', $invocation->className);
    }

    public function testAllowToGetMethodNameSetInConstructor()
    {
        $invocation = new PHPUnit_Framework_MockObject_Invocation_Object(
            'FooClass',
            'FooMethod',
            array('an_argument'),
            new stdClass());

        $this->assertSame('FooMethod', $invocation->methodName);
    }

    public function testAllowToGetObjectSetInConstructor()
    {
        $expectedObject = new stdClass();

        $invocation = new PHPUnit_Framework_MockObject_Invocation_Object(
            'FooClass',
            'FooMethod',
            array('an_argument'),
            $expectedObject);

        $this->assertSame($expectedObject, $invocation->object);
    }

    public function testAllowToGetMethodParametersSetInConstructor()
    {
        $expectedParameters = array('foo', 5, array('a', 'b'), new stdClass(), null, false);

        $invocation = new PHPUnit_Framework_MockObject_Invocation_Object(
            'FooClass',
            'FooMethod',
            $expectedParameters,
            new stdClass());

        $this->assertSame($expectedParameters, $invocation->parameters);
    }

    public function testConstructorAllowToSetFlagCloneObjectsInParameters()
    {
        $parameters = array(new stdClass());
        $cloneObjects = true;

        $invocation = new PHPUnit_Framework_MockObject_Invocation_Object(
            'FooClass',
            'FooMethod',
            $parameters,
            new stdClass(),
            $cloneObjects);

        $this->assertEquals($parameters, $invocation->parameters);
        $this->assertNotSame($parameters, $invocation->parameters);
    }
}
 
