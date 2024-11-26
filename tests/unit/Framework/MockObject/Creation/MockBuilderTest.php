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

use function assert;
use function class_exists;
use function interface_exists;
use function md5;
use function mt_rand;
use function substr;
use function trait_exists;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\Generator\CannotUseAddMethodsException;
use PHPUnit\Framework\MockObject\Generator\DuplicateMethodException;
use PHPUnit\Framework\MockObject\Generator\InvalidMethodNameException;
use PHPUnit\Framework\MockObject\Generator\NameAlreadyInUseException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AbstractClass;
use PHPUnit\TestFixture\MockObject\ExtendableClass;
use PHPUnit\TestFixture\MockObject\ExtendableClassCallingMethodInConstructor;
use PHPUnit\TestFixture\MockObject\ExtendableClassWithConstructorArguments;
use PHPUnit\TestFixture\MockObject\InterfaceWithReturnTypeDeclaration;
use PHPUnit\TestFixture\MockObject\TraitWithConcreteAndAbstractMethod;

#[CoversClass(MockBuilder::class)]
#[CoversClass(CannotUseAddMethodsException::class)]
#[CoversClass(DuplicateMethodException::class)]
#[CoversClass(InvalidMethodNameException::class)]
#[CoversClass(NameAlreadyInUseException::class)]
#[Group('test-doubles')]
#[Group('test-doubles/creation')]
#[Group('test-doubles/mock-object')]
#[Medium]
final class MockBuilderTest extends TestCase
{
    #[TestDox('setMockClassName() can be used to configure the name of the mock object class')]
    public function testCanCreateMockObjectWithSpecifiedClassName(): void
    {
        $className = 'random_' . md5((string) mt_rand());

        $double = $this->getMockBuilder(InterfaceWithReturnTypeDeclaration::class)
            ->setMockClassName($className)
            ->getMock();

        $this->assertSame($className, $double::class);
    }

    #[TestDox('setMockClassName() cannot be used to configure the name of the mock object class when a class with that name already exists')]
    public function testCannotCreateMockObjectWithSpecifiedClassNameWhenClassWithThatNameAlreadyExists(): void
    {
        $this->expectException(NameAlreadyInUseException::class);

        $this->getMockBuilder(InterfaceWithReturnTypeDeclaration::class)
            ->setMockClassName(__CLASS__)
            ->getMock();
    }

    #[TestDox('setConstructorArgs() can be used to configure constructor arguments for a partially mocked class')]
    public function testConstructorArgumentsCanBeConfiguredForPartiallyMockedClass(): void
    {
        $value = 'string';

        $double = $this->getMockBuilder(ExtendableClassWithConstructorArguments::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$value])
            ->onlyMethods([])
            ->getMock();

        $this->assertSame($value, $double->value());
    }

    #[IgnorePhpunitDeprecations]
    #[TestDox('addMethods() can be used to configure an additional method for the mock object class when the original class does not have a method of the same name')]
    public function testCanCreateMockObjectForExtendableClassWhileAddingMethodsToIt(): void
    {
        $double = $this->getMockBuilder(ExtendableClass::class)
            ->addMethods(['additionalMethod'])
            ->getMock();

        $value = 'value';

        $double->method('additionalMethod')->willReturn($value);

        $this->assertSame($value, $double->additionalMethod());
    }

    #[IgnorePhpunitDeprecations]
    #[TestDox('addMethods() cannot be used to configure an additional method for the mock object class when the original class has a method of the same name')]
    public function testCannotCreateMockObjectForExtendableClassAddingMethodToItThatItAlreadyHas(): void
    {
        $this->expectException(CannotUseAddMethodsException::class);

        $this->getMockBuilder(ExtendableClass::class)
            ->addMethods(['doSomething'])
            ->getMock();
    }

    #[IgnorePhpunitDeprecations]
    #[TestDox('addMethods() cannot be used to configure an additional method for the mock object class multiple times using the same name')]
    public function testCannotCreateMockObjectForExtendableClassAddingMultipleMethodsWithSameNameToIt(): void
    {
        $this->expectException(DuplicateMethodException::class);

        $this->getMockBuilder(ExtendableClass::class)
            ->addMethods(['additionalMethod', 'additionalMethod'])
            ->getMock();
    }

    #[IgnorePhpunitDeprecations]
    #[TestDox('addMethods() cannot be used to configure an additional method for the mock object class with invalid name')]
    public function testCannotCreateMockObjectForExtendableClassAddingMethodToItWithInvalidName(): void
    {
        $this->expectException(InvalidMethodNameException::class);

        $this->getMockBuilder(ExtendableClass::class)
            ->addMethods(['1234'])
            ->getMock();
    }

    #[IgnorePhpunitDeprecations]
    #[TestDox('getMockForAbstractClass() can be used to create a mock object for an abstract class')]
    public function testCreatesMockObjectForAbstractClassAndAllowsConfigurationOfAbstractMethods(): void
    {
        $double = $this->getMockBuilder(AbstractClass::class)
            ->getMockForAbstractClass();

        $double->expects($this->once())->method('doSomethingElse')->willReturn(true);

        $this->assertTrue($double->doSomething());
    }

    #[IgnorePhpunitDeprecations]
    #[TestDox('getMockForTrait() can be used to create a mock object for a trait')]
    public function testCreatesMockObjectForTraitAndAllowsConfigurationOfMethods(): void
    {
        $double = $this->getMockBuilder(TraitWithConcreteAndAbstractMethod::class)
            ->getMockForTrait();

        $double->method('abstractMethod')->willReturn(true);

        $this->assertTrue($double->concreteMethod());
    }

    #[TestDox('onlyMethods() can be used to configure which methods should be doubled')]
    public function testCreatesPartialMockObjectForExtendableClass(): void
    {
        $double = $this->getMockBuilder(ExtendableClass::class)
            ->onlyMethods(['doSomethingElse'])
            ->getMock();

        $double->expects($this->once())->method('doSomethingElse')->willReturn(true);

        $this->assertTrue($double->doSomething());
    }

    #[IgnorePhpunitDeprecations]
    #[TestDox('allowMockingUnknownTypes() can be used to allow mocking of unknown types')]
    public function testCreatesMockObjectForUnknownType(): void
    {
        $type = 'Type_' . substr(md5((string) mt_rand()), 0, 8);

        assert(!class_exists($type) && !interface_exists($type) && !trait_exists($type));

        $double = $this->getMockBuilder($type)
            ->allowMockingUnknownTypes()
            ->getMock();

        $this->assertInstanceOf($type, $double);
        $this->assertInstanceOf(MockObject::class, $double);
    }

    #[IgnorePhpunitDeprecations]
    public function testDefaultBehaviourCanBeConfiguredExplicitly(): void
    {
        $double = $this->getMockBuilder(ExtendableClass::class)
            ->enableOriginalConstructor()
            ->enableOriginalClone()
            ->enableAutoload()
            ->enableArgumentCloning()
            ->disableProxyingToOriginalMethods()
            ->allowMockingUnknownTypes()
            ->enableAutoReturnValueGeneration()
            ->getMock();

        $this->assertTrue($double->constructorCalled);
    }

    #[TestDox('Mocked methods can be called from the original constructor of a partially mocked class')]
    public function testOnlyMethodCalledInConstructorWorks(): void
    {
        $double = $this->getMockBuilder(ExtendableClassCallingMethodInConstructor::class)
            ->onlyMethods(['reset'])
            ->getMock();

        $double->expects($this->once())->method('reset');

        $double->second();
    }
}
