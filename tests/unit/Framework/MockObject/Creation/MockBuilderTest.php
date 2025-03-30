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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\Generator\DuplicateMethodException;
use PHPUnit\Framework\MockObject\Generator\InvalidMethodNameException;
use PHPUnit\Framework\MockObject\Generator\NameAlreadyInUseException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\ExtendableClass;
use PHPUnit\TestFixture\MockObject\ExtendableClassCallingMethodInConstructor;
use PHPUnit\TestFixture\MockObject\ExtendableClassWithConstructorArguments;
use PHPUnit\TestFixture\MockObject\InterfaceWithReturnTypeDeclaration;

#[CoversClass(MockBuilder::class)]
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
    public function testDefaultBehaviourCanBeConfiguredExplicitly(): void
    {
        $double = $this->getMockBuilder(ExtendableClass::class)
            ->enableOriginalConstructor()
            ->enableOriginalClone()
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
