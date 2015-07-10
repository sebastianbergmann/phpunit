<?php

class Framework_MockObject_Invocation_StaticTest extends PHPUnit_Framework_TestCase
{
    public function testConstructorRequiresClassAndMethodAndParameters()
    {
        new PHPUnit_Framework_MockObject_Invocation_Static(
            'FooClass',
            'FooMethod',
            array('an_argument'),
            'ReturnType'
        );
    }

    public function testAllowToGetClassNameSetInConstructor()
    {
        $invocation = new PHPUnit_Framework_MockObject_Invocation_Static(
            'FooClass',
            'FooMethod',
            array('an_argument'),
            'ReturnType'
        );

        $this->assertSame('FooClass', $invocation->className);
    }

    public function testAllowToGetMethodNameSetInConstructor()
    {
        $invocation = new PHPUnit_Framework_MockObject_Invocation_Static(
            'FooClass',
            'FooMethod',
            array('an_argument'),
            'ReturnType'
        );

        $this->assertSame('FooMethod', $invocation->methodName);
    }

    public function testAllowToGetMethodParametersSetInConstructor()
    {
        $expectedParameters = array(
          'foo', 5, array('a', 'b'), new StdClass, null, false
        );

        $invocation = new PHPUnit_Framework_MockObject_Invocation_Static(
            'FooClass',
            'FooMethod',
            $expectedParameters,
            'ReturnType'
        );

        $this->assertSame($expectedParameters, $invocation->parameters);
    }

    public function testConstructorAllowToSetFlagCloneObjectsInParameters()
    {
        $parameters = array(new StdClass);
        $cloneObjects = true;

        $invocation = new PHPUnit_Framework_MockObject_Invocation_Static(
            'FooClass',
            'FooMethod',
            $parameters,
            'ReturnType',
            $cloneObjects
        );

        $this->assertEquals($parameters, $invocation->parameters);
        $this->assertNotSame($parameters, $invocation->parameters);
    }

    public function testAllowToGetReturnTypeSetInConstructor()
    {
        $expectedReturnType = 'string';

        $invocation = new PHPUnit_Framework_MockObject_Invocation_Static(
            'FooClass',
            'FooMethod',
            array('an_argument'),
            $expectedReturnType
        );

        $this->assertSame($expectedReturnType, $invocation->returnType);
    }

}
