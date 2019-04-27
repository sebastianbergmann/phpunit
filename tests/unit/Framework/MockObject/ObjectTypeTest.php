<?php

namespace PHPUnit\Framework\MockObject;

use PHPUnit\Framework\TestCase;

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

    public function testClassFromOneNamespaceIsNotAssignableToClassInOtherNamespace()
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

    public function testNullIsAssignableToNullableType()
    {
        $someClass = new ObjectType(
            TypeName::fromQualifiedName(ParentClass::class),
            true
        );
        $this->assertTrue($someClass->isAssignable(Type::fromValue(null, true)));
    }

    public function testNullIsNotAssignableToNotNullableType()
    {
        $someClass = new ObjectType(
            TypeName::fromQualifiedName(ParentClass::class),
            false
        );
        $this->assertFalse($someClass->isAssignable(Type::fromValue(null, true)));
    }


}
