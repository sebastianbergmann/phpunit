<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\MockObject\Invocation\ObjectInvocation;
use PHPUnit\Framework\TestCase;

class ObjectInvocationTest extends TestCase
{
    public function testConstructorRequiresClassAndMethodAndParametersAndObject(): void
    {
        $this->assertInstanceOf(
            ObjectInvocation::class,
            new ObjectInvocation(
                'FooClass',
                'FooMethod',
                ['an_argument'],
                'ReturnType',
                new stdClass
            )
        );
    }

    public function testAllowToGetClassNameSetInConstructor(): void
    {
        $invocation = new ObjectInvocation(
            'FooClass',
            'FooMethod',
            ['an_argument'],
            'ReturnType',
            new stdClass
        );

        $this->assertSame('FooClass', $invocation->getClassName());
    }

    public function testAllowToGetMethodNameSetInConstructor(): void
    {
        $invocation = new ObjectInvocation(
            'FooClass',
            'FooMethod',
            ['an_argument'],
            'ReturnType',
            new stdClass
        );

        $this->assertSame('FooMethod', $invocation->getMethodName());
    }

    public function testAllowToGetObjectSetInConstructor(): void
    {
        $expectedObject = new stdClass;

        $invocation = new ObjectInvocation(
            'FooClass',
            'FooMethod',
            ['an_argument'],
            'ReturnType',
            $expectedObject
        );

        $this->assertSame($expectedObject, $invocation->getObject());
    }

    public function testAllowToGetMethodParametersSetInConstructor(): void
    {
        $expectedParameters = [
            'foo', 5, ['a', 'b'], new stdClass, null, false,
        ];

        $invocation = new ObjectInvocation(
            'FooClass',
            'FooMethod',
            $expectedParameters,
            'ReturnType',
            new stdClass
        );

        $this->assertSame($expectedParameters, $invocation->getParameters());
    }

    public function testConstructorAllowToSetFlagCloneObjectsInParameters(): void
    {
        $parameters   = [new stdClass];
        $cloneObjects = true;

        $invocation = new ObjectInvocation(
            'FooClass',
            'FooMethod',
            $parameters,
            'ReturnType',
            new stdClass,
            $cloneObjects
        );

        $this->assertEquals($parameters, $invocation->getParameters());
        $this->assertNotSame($parameters, $invocation->getParameters());
    }

    public function testAllowToGetReturnTypeSetInConstructor(): void
    {
        $expectedReturnType = 'string';

        $invocation = new ObjectInvocation(
            'FooClass',
            'FooMethod',
            ['an_argument'],
            $expectedReturnType,
            new stdClass
        );

        $this->assertSame($expectedReturnType, $invocation->getReturnType());
    }
}
