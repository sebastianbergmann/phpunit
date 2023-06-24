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

use Exception;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\MockObject\Generator\ClassIsEnumerationException;
use PHPUnit\Framework\MockObject\Generator\ClassIsFinalException;
use PHPUnit\Framework\MockObject\Generator\ClassIsReadonlyException;
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
    final public function testCanBeCreatedForInterface(): void
    {
        $double = $this->createTestDouble(AnInterface::class);

        $this->assertInstanceOf(AnInterface::class, $double);
        $this->assertInstanceOf(Stub::class, $double);
    }

    final public function testCanBeCreatedForIntersectionOfInterfaces(): void
    {
        $double = $this->createTestDoubleForIntersection([AnInterface::class, AnotherInterface::class]);

        $this->assertInstanceOf(AnInterface::class, $double);
        $this->assertInstanceOf(AnotherInterface::class, $double);
        $this->assertInstanceOf(Stub::class, $double);
    }

    final public function testCanBeCreatedForClassThatCanBeExtended(): void
    {
        $double = $this->createTestDouble(ExtendableClass::class);

        $this->assertInstanceOf(ExtendableClass::class, $double);
        $this->assertInstanceOf(Stub::class, $double);
    }

    final public function testCannotBeCreatedForFinalClass(): void
    {
        $this->expectException(ClassIsFinalException::class);

        $this->createTestDouble(FinalClass::class);
    }

    #[RequiresPhp('8.2')]
    final public function testCannotBeCreatedForReadonlyClass(): void
    {
        $this->expectException(ClassIsReadonlyException::class);

        $this->createTestDouble(ReadonlyClass::class);
    }

    final public function testCannotBeCreatedForEnumeration(): void
    {
        $this->expectException(ClassIsEnumerationException::class);

        $this->createTestDouble(Enumeration::class);
    }

    final public function testMethodReturnsNullWhenReturnValueIsNullableAndNoReturnValueIsConfigured(): void
    {
        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $this->assertNull($double->returnsNullOrString());
    }

    final public function testMethodReturnsGeneratedValueWhenReturnValueGenerationIsEnabledAndNoReturnValueIsConfigured(): void
    {
        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $this->assertFalse($double->doSomething());
    }

    final public function testToStringMethodReturnsEmptyStringWhenReturnValueGenerationIsDisabledAndNoReturnValueIsConfigured(): void
    {
        $double = $this->getMockBuilder(InterfaceWithReturnTypeDeclaration::class)
            ->disableAutoReturnValueGeneration()
            ->getMock();

        $this->assertSame('', $double->__toString());
    }

    final public function testMethodDoesNotReturnValueWhenReturnValueGenerationIsDisabledAndNoReturnValueIsConfigured(): void
    {
        $double = $this->getMockBuilder(InterfaceWithReturnTypeDeclaration::class)
            ->disableAutoReturnValueGeneration()
            ->getMock();

        $this->expectException(ReturnValueNotConfiguredException::class);
        $this->expectExceptionMessage('No return value is configured for ' . InterfaceWithReturnTypeDeclaration::class . '::doSomething() and return value generation is disabled');

        $double->doSomething();
    }

    final public function testMethodReturnsConfiguredValueWhenReturnValueIsConfigured(): void
    {
        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $double->method('doSomething')->willReturn(true);

        $this->assertTrue($double->doSomething());
    }

    final public function testConfiguredReturnValueMustBeCompatibleWithReturnTypeDeclaration(): void
    {
        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $this->expectException(IncompatibleReturnValueException::class);

        $double->method('doSomething')->willReturn(null);
    }

    final public function testMethodCanBeConfiguredToReturnOneOfItsArguments(): void
    {
        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $double->method('doSomethingElse')->willReturnArgument(0);

        $this->assertSame(123, $double->doSomethingElse(123));
    }

    final public function testMethodCanBeConfiguredToReturnSelfReference(): void
    {
        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $double->method('selfReference')->willReturnSelf();

        $this->assertSame($double, $double->selfReference());
    }

    final public function testMethodCanBeConfiguredToReturnReference(): void
    {
        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $double->method('doSomething')->willReturnReference($value);

        $value = true;

        $this->assertSame($value, $double->doSomething());
    }

    final public function testMethodCanBeConfiguredToReturnValuesBasedOnArgumentMapping(): void
    {
        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $double->method('doSomethingElse')->willReturnMap([[1, 2], [3, 4]]);

        $this->assertSame(2, $double->doSomethingElse(1));
        $this->assertSame(4, $double->doSomethingElse(3));
    }

    final public function testMethodCanBeConfiguredToReturnValuesUsingCallback(): void
    {
        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $double->method('doSomethingElse')->willReturnCallback(
            static function (int $x)
            {
                return match ($x) {
                    1 => 2,
                    3 => 4,
                };
            },
        );

        $this->assertSame(2, $double->doSomethingElse(1));
        $this->assertSame(4, $double->doSomethingElse(3));
    }

    final public function testMethodCanBeConfiguredToReturnDifferentValuesOnConsecutiveCalls(): void
    {
        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $double->method('doSomething')->willReturnOnConsecutiveCalls(false, true, false, true);

        $this->assertFalse($double->doSomething());
        $this->assertTrue($double->doSomething());
        $this->assertFalse($double->doSomething());
        $this->assertTrue($double->doSomething());
    }

    final public function testMethodCanBeConfiguredToThrowAnException(): void
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
