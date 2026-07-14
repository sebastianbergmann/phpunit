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

use function md5;
use function mt_rand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\Generator\NameAlreadyInUseException;
use PHPUnit\Framework\MockObject\Runtime\PropertyHook;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\ExtendableClass;
use PHPUnit\TestFixture\MockObject\ExtendableClassWithConstructorArguments;
use PHPUnit\TestFixture\MockObject\ExtendableClassWithPropertiesThatCannotBeDoubled;
use PHPUnit\TestFixture\MockObject\ExtendableClassWithPropertiesWithoutHooks;
use PHPUnit\TestFixture\MockObject\InterfaceWithReturnTypeDeclaration;

#[CoversClass(TestStubBuilder::class)]
#[CoversClass(TestDoubleBuilder::class)]
#[CoversClass(PropertyCannotBeDoubledException::class)]
#[CoversMethod(TestCase::class, 'getStubBuilder')]
#[Group('test-doubles')]
#[Group('test-doubles/creation')]
#[Group('test-doubles/test-stub')]
#[TestDox('TestStubBuilder')]
#[Medium]
final class TestStubBuilderTest extends TestCase
{
    #[TestDox('setStubClassName() can be used to configure the name of the test stub class')]
    public function testCanCreateTestStubWithSpecifiedClassName(): void
    {
        $className = 'random_' . md5((string) mt_rand());

        $double = $this->getStubBuilder(InterfaceWithReturnTypeDeclaration::class)
            ->setStubClassName($className)
            ->getStub();

        $this->assertSame($className, $double::class);
    }

    #[TestDox('setStubClassName() cannot be used to configure the name of the test stub class when a class with that name already exists')]
    public function testCannotCreateTestStubWithSpecifiedClassNameWhenClassWithThatNameAlreadyExists(): void
    {
        $this->expectException(NameAlreadyInUseException::class);

        $this->getStubBuilder(InterfaceWithReturnTypeDeclaration::class)
            ->setStubClassName('stdClass')
            ->getStub();
    }

    #[TestDox('setConstructorArgs() can be used to configure constructor arguments for a partially stubbed class')]
    public function testConstructorArgumentsCanBeConfiguredForPartiallyStubbedClass(): void
    {
        $value = 'string';

        $double = $this->getStubBuilder(ExtendableClassWithConstructorArguments::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs([$value])
            ->onlyMethods([])
            ->getStub();

        $this->assertSame($value, $double->value());
    }

    #[TestDox('onlyMethods() can be used to configure which methods should be doubled')]
    public function testCreatesPartialTestStubForExtendableClass(): void
    {
        $double = $this->getStubBuilder(ExtendableClass::class)
            ->onlyMethods(['doSomethingElse'])
            ->getStub();

        $double->method('doSomethingElse')->willReturn(true);

        $this->assertTrue($double->doSomething());
    }

    public function testDefaultBehaviourCanBeConfiguredExplicitly(): void
    {
        $double = $this->getStubBuilder(ExtendableClass::class)
            ->enableOriginalConstructor()
            ->enableOriginalClone()
            ->enableAutoReturnValueGeneration()
            ->getStub();

        $this->assertTrue($double->constructorCalled);
    }

    #[TestDox('doubleProperties() can be used to double a property that does not declare hooks')]
    public function testGetHookForPropertyWithoutHooksCanBeConfiguredWhenPropertyIsDoubled(): void
    {
        $double = $this->getStubBuilder(ExtendableClassWithPropertiesWithoutHooks::class)
            ->doubleProperties(['property'])
            ->getStub();

        $double->method(PropertyHook::get('property'))->willReturn('value');

        $this->assertSame('value', $double->property);
    }

    #[TestDox('doubleProperties() does not affect properties that are not doubled')]
    public function testPropertyThatIsNotDoubledBehavesLikeRegularProperty(): void
    {
        $double = $this->getStubBuilder(ExtendableClassWithPropertiesWithoutHooks::class)
            ->doubleProperties(['property'])
            ->getStub();

        $double->otherProperty = 'value';

        $this->assertSame('value', $double->otherProperty);
    }

    #[TestDox('doubleProperties() cannot be used for a property that does not exist')]
    public function testPropertyThatDoesNotExistCannotBeDoubled(): void
    {
        $this->expectException(PropertyCannotBeDoubledException::class);
        $this->expectExceptionMessageIs('Trying to double property "doesNotExist" of class "PHPUnit\TestFixture\MockObject\ExtendableClassWithPropertiesWithoutHooks" with doubleProperties(), but it does not exist');

        $this->getStubBuilder(ExtendableClassWithPropertiesWithoutHooks::class)
            ->doubleProperties(['doesNotExist']);
    }

    #[TestDox('doubleProperties() cannot be used for a property that is not public')]
    public function testPropertyThatIsNotPublicCannotBeDoubled(): void
    {
        $this->expectException(PropertyCannotBeDoubledException::class);
        $this->expectExceptionMessageIs('Trying to double property "protectedProperty" of class "PHPUnit\TestFixture\MockObject\ExtendableClassWithPropertiesThatCannotBeDoubled" with doubleProperties(), but it is not public');

        $this->getStubBuilder(ExtendableClassWithPropertiesThatCannotBeDoubled::class)
            ->doubleProperties(['protectedProperty']);
    }

    #[TestDox('doubleProperties() cannot be used for a property that is static')]
    public function testPropertyThatIsStaticCannotBeDoubled(): void
    {
        $this->expectException(PropertyCannotBeDoubledException::class);
        $this->expectExceptionMessageIs('Trying to double property "staticProperty" of class "PHPUnit\TestFixture\MockObject\ExtendableClassWithPropertiesThatCannotBeDoubled" with doubleProperties(), but it is static');

        $this->getStubBuilder(ExtendableClassWithPropertiesThatCannotBeDoubled::class)
            ->doubleProperties(['staticProperty']);
    }

    #[TestDox('doubleProperties() cannot be used for a property that is readonly')]
    public function testPropertyThatIsReadonlyCannotBeDoubled(): void
    {
        $this->expectException(PropertyCannotBeDoubledException::class);
        $this->expectExceptionMessageIs('Trying to double property "readonlyProperty" of class "PHPUnit\TestFixture\MockObject\ExtendableClassWithPropertiesThatCannotBeDoubled" with doubleProperties(), but it is readonly');

        $this->getStubBuilder(ExtendableClassWithPropertiesThatCannotBeDoubled::class)
            ->doubleProperties(['readonlyProperty']);
    }

    #[TestDox('doubleProperties() cannot be used for a property that is final')]
    public function testPropertyThatIsFinalCannotBeDoubled(): void
    {
        $this->expectException(PropertyCannotBeDoubledException::class);
        $this->expectExceptionMessageIs('Trying to double property "finalProperty" of class "PHPUnit\TestFixture\MockObject\ExtendableClassWithPropertiesThatCannotBeDoubled" with doubleProperties(), but it is final');

        $this->getStubBuilder(ExtendableClassWithPropertiesThatCannotBeDoubled::class)
            ->doubleProperties(['finalProperty']);
    }

    #[TestDox('doubleProperties() cannot be used for a property that does not declare a type')]
    public function testPropertyThatDoesNotDeclareTypeCannotBeDoubled(): void
    {
        $this->expectException(PropertyCannotBeDoubledException::class);
        $this->expectExceptionMessageIs('Trying to double property "untypedProperty" of class "PHPUnit\TestFixture\MockObject\ExtendableClassWithPropertiesThatCannotBeDoubled" with doubleProperties(), but it does not declare a type');

        $this->getStubBuilder(ExtendableClassWithPropertiesThatCannotBeDoubled::class)
            ->doubleProperties(['untypedProperty']);
    }
}
