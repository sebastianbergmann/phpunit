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

class Framework_MockObject_Invocation_StaticTest extends TestCase
{
    public function testConstructorRequiresClassAndMethodAndParameters()
    {
        $this->assertInstanceOf(
            PHPUnit_Framework_MockObject_Invocation_Static::class,
            new PHPUnit_Framework_MockObject_Invocation_Static(
                'FooClass',
                'FooMethod',
                ['an_argument'],
                'ReturnType'
            )
        );
    }

    public function testAllowToGetClassNameSetInConstructor()
    {
        $invocation = new PHPUnit_Framework_MockObject_Invocation_Static(
            'FooClass',
            'FooMethod',
            ['an_argument'],
            'ReturnType'
        );

        $this->assertSame('FooClass', $invocation->className);
    }

    public function testAllowToGetMethodNameSetInConstructor()
    {
        $invocation = new PHPUnit_Framework_MockObject_Invocation_Static(
            'FooClass',
            'FooMethod',
            ['an_argument'],
            'ReturnType'
        );

        $this->assertSame('FooMethod', $invocation->methodName);
    }

    public function testAllowToGetMethodParametersSetInConstructor()
    {
        $expectedParameters = [
          'foo', 5, ['a', 'b'], new stdClass, null, false
        ];

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
        $parameters   = [new stdClass];
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
            ['an_argument'],
            $expectedReturnType
        );

        $this->assertSame($expectedReturnType, $invocation->returnType);
    }
}
