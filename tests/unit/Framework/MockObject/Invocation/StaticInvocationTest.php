<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPUnit\Framework\MockObject\Invocation\StaticInvocation;
use PHPUnit\Framework\TestCase;

class StaticInvocationTest extends TestCase
{
    public function testConstructorRequiresClassAndMethodAndParameters(): void
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

    public function testAllowToGetClassNameSetInConstructor(): void
    {
        $invocation = new StaticInvocation(
            'FooClass',
            'FooMethod',
            ['an_argument'],
            'ReturnType'
        );

        $this->assertSame('FooClass', $invocation->getClassName());
    }

    public function testAllowToGetMethodNameSetInConstructor(): void
    {
        $invocation = new StaticInvocation(
            'FooClass',
            'FooMethod',
            ['an_argument'],
            'ReturnType'
        );

        $this->assertSame('FooMethod', $invocation->getMethodName());
    }

    public function testAllowToGetMethodParametersSetInConstructor(): void
    {
        $expectedParameters = [
            'foo', 5, ['a', 'b'], new stdClass, null, false,
        ];

        $invocation = new StaticInvocation(
            'FooClass',
            'FooMethod',
            $expectedParameters,
            'ReturnType'
        );

        $this->assertSame($expectedParameters, $invocation->getParameters());
    }

    public function testConstructorAllowToSetFlagCloneObjectsInParameters(): void
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

    public function testAllowToGetReturnTypeSetInConstructor(): void
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

    public function testToStringWillReturnEmptyString(): void
    {
        $expectedReturnType = 'string';

        $invocation = new StaticInvocation(
            'FooClass',
            '__toString',
            [],
            ''
        );

        $this->assertSame($expectedReturnType, $invocation->getReturnType());
        $this->assertSame('', $invocation->generateReturnValue());
    }
}
