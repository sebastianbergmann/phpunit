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
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AbstractClass;
use PHPUnit\TestFixture\MockObject\ExtendableClass;
use PHPUnit\TestFixture\MockObject\InterfaceWithReturnTypeDeclaration;
use PHPUnit\TestFixture\MockObject\TraitWithConcreteAndAbstractMethod;

#[CoversClass(MockBuilder::class)]
#[CoversClass(CannotUseAddMethodsException::class)]
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
    public function testCannotCreateMockObjectForExtendableClassAddingMethodsToItThatItAlreadyHas(): void
    {
        $this->expectException(CannotUseAddMethodsException::class);

        $this->getMockBuilder(ExtendableClass::class)
            ->addMethods(['doSomething'])
            ->getMock();
    }

    #[IgnorePhpunitDeprecations]
    #[TestDox('getMockForAbstractClass() can be used to create a mock object for an abstract class')]
    public function testCreatesMockObjectForAbstractClassAndAllowsConfigurationOfAbstractMethods(): void
    {
        $mock = $this->getMockBuilder(AbstractClass::class)
            ->getMockForAbstractClass();

        $mock->expects($this->once())->method('doSomethingElse')->willReturn(true);

        $this->assertTrue($mock->doSomething());
    }

    #[IgnorePhpunitDeprecations]
    #[TestDox('getMockForTrait() can be used to create a mock object for a trait')]
    public function testCreatesMockObjectForTraitAndAllowsConfigurationOfMethods(): void
    {
        $mock = $this->getMockBuilder(TraitWithConcreteAndAbstractMethod::class)
            ->getMockForTrait();

        $mock->method('abstractMethod')->willReturn(true);

        $this->assertTrue($mock->concreteMethod());
    }

    #[IgnorePhpunitDeprecations]
    #[TestDox('onlyMethods() can be used to configure which methods should be doubled')]
    public function testCreatesPartialMockObjectForExtendableClass(): void
    {
        $mock = $this->getMockBuilder(ExtendableClass::class)
            ->onlyMethods(['doSomethingElse'])
            ->getMock();

        $mock->expects($this->once())->method('doSomethingElse')->willReturn(true);

        $this->assertTrue($mock->doSomething());
    }
}
