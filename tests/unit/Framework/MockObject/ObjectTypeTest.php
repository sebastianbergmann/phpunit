<?php declare(strict_types=1);
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
use PHPUnit\TestFixture\MockObject\ChildClass;
use PHPUnit\TestFixture\MockObject\ParentClass;

/**
 * @covers \PHPUnit\Framework\MockObject\ObjectType
 */
class ObjectTypeTest extends TestCase
{
    /**
     * @var ObjectType
     */
    private $childClass;

    /**
     * @var ObjectType
     */
    private $parentClass;

    protected function setUp(): void
    {
        parent::setUp();
        $this->childClass = new ObjectType(
            TypeName::fromQualifiedName(ChildClass::class),
            false
        );
        $this->parentClass = new ObjectType(
            TypeName::fromQualifiedName(ParentClass::class),
            false
        );
    }

    public function testParentIsNotAssignableToChild(): void
    {
        $this->assertFalse($this->childClass->isAssignable($this->parentClass));
    }

    public function testChildIsAssignableToParent(): void
    {
        $this->assertTrue($this->parentClass->isAssignable($this->childClass));
    }

    public function testClassIsAssignableToSelf(): void
    {
        $this->assertTrue($this->parentClass->isAssignable($this->parentClass));
    }

    public function testSimpleTypeIsNotAssignableToClass(): void
    {
        $this->assertFalse($this->parentClass->isAssignable(new SimpleType('int', false)));
    }

    public function testClassFromOneNamespaceIsNotAssignableToClassInOtherNamespace(): void
    {
        $classFromNamespaceA = new ObjectType(
            TypeName::fromQualifiedName(\someNamespaceA\NamespacedClass::class),
            false
        );
        $classFromNamespaceB = new ObjectType(
            TypeName::fromQualifiedName(\someNamespaceB\NamespacedClass::class),
            false
        );
        $this->assertFalse($classFromNamespaceA->isAssignable($classFromNamespaceB));
    }

    public function testNullIsAssignableToNullableType(): void
    {
        $someClass = new ObjectType(
            TypeName::fromQualifiedName(ParentClass::class),
            true
        );
        $this->assertTrue($someClass->isAssignable(Type::fromValue(null, true)));
    }

    public function testNullIsNotAssignableToNotNullableType(): void
    {
        $someClass = new ObjectType(
            TypeName::fromQualifiedName(ParentClass::class),
            false
        );
        $this->assertFalse($someClass->isAssignable(Type::fromValue(null, true)));
    }

    public function testPreservesNullNotAllowed(): void
    {
        $someClass = new ObjectType(
            TypeName::fromQualifiedName(ParentClass::class),
            false
        );
        $this->assertFalse($someClass->allowsNull());
    }

    public function testPreservesNullAllowed(): void
    {
        $someClass = new ObjectType(
            TypeName::fromQualifiedName(ParentClass::class),
            true
        );
        $this->assertTrue($someClass->allowsNull());
    }
}
