<?php
declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject;

use PHPUnit\Framework\TestCase;

class OriginalTypeReflectionTest extends TestCase
{
    public function testPrologueIsEmpty(): void
    {
        $originalType = new OriginalTypeReflection(
            new \ReflectionClass(\stdClass::class)
        );
        $this->assertEquals('', $originalType->getCodePrologue());
    }

    public function testEpilogueIsEmpty(): void
    {
        $originalType = new OriginalTypeReflection(
            new \ReflectionClass(\stdClass::class)
        );
        $this->assertEquals('', $originalType->getCodeEpilogue());
    }

    public function testHasMethodOfClass(): void
    {
        $originalType = new OriginalTypeReflection(
            new \ReflectionClass(\IteratorAggregate::class)
        );
        $this->assertTrue($originalType->hasMethod('getIterator'));
    }

    public function testDoesNotHaveMethodNotDefinedInClass(): void
    {
        $originalType = new OriginalTypeReflection(
            new \ReflectionClass(\IteratorAggregate::class)
        );
        $this->assertFalse($originalType->hasMethod('__toString'));
    }

    public function testGetMethodDefinedInClass(): void
    {
        $originalType = new OriginalTypeReflection(
            new \ReflectionClass(\IteratorAggregate::class)
        );
        $method = $originalType->getMethod('getIterator');
        $this->assertEquals('getIterator', $method->getName());
        $this->assertEquals(\IteratorAggregate::class, $method->getDeclaringClass()->getName());
    }

    public function testFailToGetMethodNotDefinedInClass(): void
    {
        $originalType = new OriginalTypeReflection(
            new \ReflectionClass(\IteratorAggregate::class)
        );
        $this->expectException(\OutOfBoundsException::class);
        $originalType->getMethod('foo');
    }

    public function testGetAllMethods(): void
    {
        $originalType = new OriginalTypeReflection(
            new \ReflectionClass(\IteratorAggregate::class)
        );
        $methods = $originalType->getMethods();
        $this->assertCount(1, $methods);
        /** @var \ReflectionMethod $method */
        $method = $methods[0];
        $this->assertEquals('getIterator', $method->getName());
        $this->assertEquals(\IteratorAggregate::class, $method->getDeclaringClass()->getName());
    }

    public function testInterfaceIsInterface(): void
    {
        $originalType = new OriginalTypeReflection(
            new \ReflectionClass(\IteratorAggregate::class)
        );
        $this->assertTrue($originalType->isInterface());
    }

    public function testClassIsNoInterface(): void
    {
        $originalType = new OriginalTypeReflection(
            new \ReflectionClass(\stdClass::class)
        );
        $this->assertFalse($originalType->isInterface());
    }

    public function testFinalClassIsFinal(): void
    {
        $originalType = new OriginalTypeReflection(
            new \ReflectionClass(OriginalTypeReflection::class)
        );
        $this->assertTrue($originalType->isFinal());
    }

    public function testUnFinalClassIsNotFinal(): void
    {
        $originalType = new OriginalTypeReflection(
            new \ReflectionClass(\stdClass::class)
        );
        $this->assertFalse($originalType->isFinal());
    }

    public function testDetectImplementedInterface(): void
    {
        $originalType = new OriginalTypeReflection(
            new \ReflectionClass(OriginalTypeReflection::class)
        );
        $this->assertTrue($originalType->implementsInterface(OriginalType::class));
    }

    public function testDetectUnImplementedInterface(): void
    {
        $originalType = new OriginalTypeReflection(
            new \ReflectionClass(OriginalTypeReflection::class)
        );
        $this->assertFalse($originalType->implementsInterface(\IteratorAggregate::class));
    }

    public function testPreservesName(): void
    {
        $originalType = new OriginalTypeReflection(
            new \ReflectionClass(\stdClass::class)
        );
        $this->assertEquals(
            TypeName::fromQualifiedName(\stdClass::class),
            $originalType->getName()
        );
    }
}
