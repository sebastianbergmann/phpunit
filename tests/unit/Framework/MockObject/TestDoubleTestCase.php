<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace unit\Framework\MockObject;

use Exception;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\MockObject\ClassIsEnumerationException;
use PHPUnit\Framework\MockObject\ClassIsFinalException;
use PHPUnit\Framework\MockObject\ClassIsReadonlyException;
use PHPUnit\Framework\MockObject\IncompatibleReturnValueException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AnInterface;
use PHPUnit\TestFixture\MockObject\AnotherInterface;
use PHPUnit\TestFixture\MockObject\Enumeration;
use PHPUnit\TestFixture\MockObject\ExtendableClass;
use PHPUnit\TestFixture\MockObject\FinalClass;
use PHPUnit\TestFixture\MockObject\InterfaceWithReturnTypeDeclaration;
use PHPUnit\TestFixture\MockObject\ReadonlyClass;

abstract class TestDoubleTestCase extends TestCase
{
    public function testCanBeCreatedForInterface(): void
    {
        $double = $this->createTestDouble(AnInterface::class);

        $this->assertInstanceOf(AnInterface::class, $double);
        $this->assertInstanceOf(Stub::class, $double);
    }

    public function testCanBeCreatedForIntersectionOfInterfaces(): void
    {
        $double = $this->createTestDoubleForIntersection([AnInterface::class, AnotherInterface::class]);

        $this->assertInstanceOf(AnInterface::class, $double);
        $this->assertInstanceOf(AnotherInterface::class, $double);
        $this->assertInstanceOf(Stub::class, $double);
    }

    public function testCanBeCreatedForClassThatCanBeExtended(): void
    {
        $double = $this->createTestDouble(ExtendableClass::class);

        $this->assertInstanceOf(ExtendableClass::class, $double);
        $this->assertInstanceOf(Stub::class, $double);
    }

    public function testCannotBeCreatedForFinalClass(): void
    {
        $this->expectException(ClassIsFinalException::class);

        $this->createTestDouble(FinalClass::class);
    }

    #[RequiresPhp('8.2')]
    public function testCannotBeCreatedForReadonlyClass(): void
    {
        $this->expectException(ClassIsReadonlyException::class);

        $this->createTestDouble(ReadonlyClass::class);
    }

    public function testCannotBeCreatedForEnumeration(): void
    {
        $this->expectException(ClassIsEnumerationException::class);

        $this->createTestDouble(Enumeration::class);
    }

    public function testMethodReturnsGeneratedValueWhenNoReturnValueIsConfigured(): void
    {
        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $this->assertFalse($double->doSomething());
    }

    public function testMethodReturnsConfiguredValueWhenReturnValueIsConfigured(): void
    {
        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $double->method('doSomething')->willReturn(true);

        $this->assertTrue($double->doSomething());
    }

    public function testConfiguredReturnValueMustBeCompatibleWithReturnTypeDeclaration(): void
    {
        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $this->expectException(IncompatibleReturnValueException::class);

        $double->method('doSomething')->willReturn(null);
    }

    public function testMethodCanBeConfiguredToReturnOneOfItsArguments(): void
    {
        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $double->method('doSomethingElse')->willReturnArgument(0);

        $this->assertSame(123, $double->doSomethingElse(123));
    }

    public function testMethodCanBeConfiguredToReturnSelfReference(): void
    {
        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $double->method('selfReference')->willReturnSelf();

        $this->assertSame($double, $double->selfReference());
    }

    public function testMethodCanBeConfiguredToReturnValuesBasedOnArgumentMapping(): void
    {
        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $double->method('doSomethingElse')->willReturnMap([[1, 2], [3, 4]]);

        $this->assertSame(2, $double->doSomethingElse(1));
        $this->assertSame(4, $double->doSomethingElse(3));
    }

    public function testMethodCanBeConfiguredToThrowAnException(): void
    {
        $expectedException = new Exception('exception configured using throwException()');

        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $double->method('doSomething')->will($this->throwException($expectedException));

        try {
            $double->doSomething();
        } catch (Exception $actualException) {
            $this->assertSame($expectedException, $actualException);

            return;
        }

        $this->fail();
    }

    /**
     * @psalm-template RealInstanceType of object
     *
     * @psalm-param class-string<RealInstanceType> $type
     *
     * @psalm-return (Stub|MockObject)&RealInstanceType
     *
     * @noinspection PhpParameterNameChangedDuringInheritanceInspection
     */
    abstract protected function createTestDouble(string $type): object;

    /**
     * @psalm-param list<class-string> $interfaces
     */
    abstract protected function createTestDoubleForIntersection(array $interfaces): object;
}
