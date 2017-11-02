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
use PHPUnit\Framework\MockObject\Invocation\StaticInvocation;

class StaticInvocationTest extends TestCase
{
    public function testConstructorRequiresClassAndMethodAndParameters()
    {
        $this->assertInstanceOf(
            StaticInvocation::class,
            new StaticInvocation(
                'FooClass',
                'FooMethod',
                ['an_argument'],
                'ReturnType'
            )
        );
    }

    public function testAllowToGetClassNameSetInConstructor()
    {
        $invocation = new StaticInvocation(
            'FooClass',
            'FooMethod',
            ['an_argument'],
            'ReturnType'
        );

        $this->assertSame('FooClass', $invocation->getClassName());
    }

    public function testAllowToGetMethodNameSetInConstructor()
    {
        $invocation = new StaticInvocation(
            'FooClass',
            'FooMethod',
            ['an_argument'],
            'ReturnType'
        );

        $this->assertSame('FooMethod', $invocation->getMethodName());
    }

    public function testAllowToGetMethodParametersSetInConstructor()
    {
        $expectedParameters = [
          'foo', 5, ['a', 'b'], new stdClass, null, false
        ];

        $invocation = new StaticInvocation(
            'FooClass',
            'FooMethod',
            $expectedParameters,
            'ReturnType'
        );

        $this->assertSame($expectedParameters, $invocation->getParameters());
    }

    public function testConstructorAllowToSetFlagCloneObjectsInParameters()
    {
        $parameters   = [new stdClass];
        $cloneObjects = true;

        $invocation = new StaticInvocation(
            'FooClass',
            'FooMethod',
            $parameters,
            'ReturnType',
            $cloneObjects
        );

        $this->assertEquals($parameters, $invocation->getParameters());
        $this->assertNotSame($parameters, $invocation->getParameters());
    }

    public function testAllowToGetReturnTypeSetInConstructor()
    {
        $expectedReturnType = 'string';

        $invocation = new StaticInvocation(
            'FooClass',
            'FooMethod',
            ['an_argument'],
            $expectedReturnType
        );

        $this->assertSame($expectedReturnType, $invocation->getReturnType());
    }
}
