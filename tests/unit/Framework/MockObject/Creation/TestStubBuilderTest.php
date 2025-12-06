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
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\ExtendableClass;
use PHPUnit\TestFixture\MockObject\ExtendableClassWithConstructorArguments;
use PHPUnit\TestFixture\MockObject\InterfaceWithReturnTypeDeclaration;

#[CoversClass(TestStubBuilder::class)]
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
            ->setStubClassName(__CLASS__)
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
}
