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
use PHPUnit\Framework\Attributes\IgnorePhpunitDeprecations;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\ExtendableClassWithCloneMethod;
use PHPUnit\TestFixture\MockObject\ExtendableReadonlyClassWithCloneMethod;
use PHPUnit\TestFixture\MockObject\InterfaceWithMethodThatExpectsObject;
use PHPUnit\TestFixture\MockObject\InterfaceWithMethodThatHasDefaultParameterValues;
use PHPUnit\TestFixture\MockObject\InterfaceWithNeverReturningMethod;
use PHPUnit\TestFixture\MockObject\InterfaceWithReturnTypeDeclaration;
use stdClass;

abstract class TestDoubleTestCase extends TestCase
{
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

    #[TestDox('__toString() method returns empty string when return value generation is disabled and no return value is configured')]
    #[IgnorePhpunitDeprecations]
    final public function testToStringMethodReturnsEmptyStringWhenReturnValueGenerationIsDisabledAndNoReturnValueIsConfigured(): void
    {
        $double = $this->getMockBuilder(InterfaceWithReturnTypeDeclaration::class)
            ->disableAutoReturnValueGeneration()
            ->getMock();

        $this->assertSame('', $double->__toString());
    }

    #[IgnorePhpunitDeprecations]
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

    public function testObjectsPassedAsArgumentAreNotClonedByDefault(): void
    {
        $object = new stdClass;

        $double = $this->createTestDouble(InterfaceWithMethodThatExpectsObject::class);

        $double->method('doSomething')->willReturnArgument(0);

        $this->assertSame($object, $double->doSomething($object));
    }

    #[IgnorePhpunitDeprecations]
    public function testCloningOfObjectsPassedAsArgumentCanBeEnabled(): void
    {
        $object = new stdClass;

        $double = $this->getMockBuilder(InterfaceWithMethodThatExpectsObject::class)
            ->enableArgumentCloning()
            ->getMock();

        $double->method('doSomething')->willReturnArgument(0);

        $this->assertNotSame($object, $double->doSomething($object));
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

    final public function testMethodWithDefaultParameterValuesCanBeConfiguredToReturnValuesBasedOnArgumentMapping(): void
    {
        $double = $this->createTestDouble(InterfaceWithMethodThatHasDefaultParameterValues::class);

        $double->method('doSomething')->willReturnMap([[1, 2, 3], [4, 5, 6]]);

        $this->assertSame(3, $double->doSomething(1, 2));
        $this->assertSame(6, $double->doSomething(4, 5));
    }

    final public function testMethodWithDefaultParameterValuesCanBeConfiguredToReturnValuesBasedOnArgumentMappingThatOmitsDefaultValues(): void
    {
        $double = $this->createTestDouble(InterfaceWithMethodThatHasDefaultParameterValues::class);

        $double->method('doSomething')->willReturnMap([[1, 2], [3, 4]]);

        $this->assertSame(2, $double->doSomething(1));
        $this->assertSame(4, $double->doSomething(3));
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

        $double->method('doSomething')->willReturn(false, true, false, true);

        $this->assertFalse($double->doSomething());
        $this->assertTrue($double->doSomething());
        $this->assertFalse($double->doSomething());
        $this->assertTrue($double->doSomething());
    }

    final public function testMethodCanBeConfiguredToReturnDifferentValuesAndThrowExceptionsOnConsecutiveCalls(): void
    {
        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $double->method('doSomething')->willReturnOnConsecutiveCalls(
            false,
            true,
            $this->throwException(new Exception),
        );

        $this->assertFalse($double->doSomething());
        $this->assertTrue($double->doSomething());

        $this->expectException(Exception::class);

        $double->doSomething();
    }

    final public function testMethodCanBeConfiguredToThrowAnException(): void
    {
        $expectedException = new Exception('exception configured using throwException()');

        $double = $this->createTestDouble(InterfaceWithReturnTypeDeclaration::class);

        $double->method('doSomething')->willThrowException($expectedException);

        try {
            $double->doSomething();
        } catch (Exception $actualException) {
            $this->assertSame($expectedException, $actualException);

            return;
        }

        $this->fail();
    }

    final public function testMethodWithNeverReturnTypeDeclarationThrowsException(): void
    {
        $double = $this->createTestDouble(InterfaceWithNeverReturningMethod::class);

        $this->expectException(NeverReturningMethodException::class);
        $this->expectExceptionMessage('Method PHPUnit\TestFixture\MockObject\InterfaceWithNeverReturningMethod::m() is declared to never return');

        $double->m();
    }

    #[TestDox('Original __clone() method is not called by default when test double object is cloned')]
    final public function testOriginalCloneMethodIsNotCalledByDefaultWhenTestDoubleObjectIsCloned(): void
    {
        $double = clone $this->createTestDouble(ExtendableClassWithCloneMethod::class);

        $this->assertFalse($double->doSomething());
    }

    #[TestDox('Original __clone() method can optionally be called when test double object is cloned')]
    final public function testOriginalCloneMethodCanOptionallyBeCalledWhenTestDoubleObjectIsCloned(): void
    {
        $double = $this->getMockBuilder(ExtendableClassWithCloneMethod::class)->enableOriginalClone()->getMock();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(ExtendableClassWithCloneMethod::class . '::__clone');

        clone $double;
    }

    #[TestDox('Original __clone() method is not called by default when test double object is cloned (readonly class)')]
    #[RequiresPhp('8.3')]
    final public function testOriginalCloneMethodIsNotCalledByDefaultWhenTestDoubleObjectOfReadonlyClassIsCloned(): void
    {
        $double = clone $this->createTestDouble(ExtendableReadonlyClassWithCloneMethod::class);

        $this->assertFalse($double->doSomething());
    }

    #[TestDox('Original __clone() method can optionally be called when test double object is cloned (readonly class)')]
    #[RequiresPhp('8.3')]
    final public function testOriginalCloneMethodCanOptionallyBeCalledWhenTestDoubleObjectOfReadonlyClassIsCloned(): void
    {
        $double = $this->getMockBuilder(ExtendableReadonlyClassWithCloneMethod::class)->enableOriginalClone()->getMock();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(ExtendableReadonlyClassWithCloneMethod::class . '::__clone');

        clone $double;
    }

    /**
     * @psalm-template RealInstanceType of object
     *
     * @psalm-param class-string<RealInstanceType> $type
     *
     * @psalm-return (Stub|MockObject)&RealInstanceType
     */
    abstract protected function createTestDouble(string $type): object;
}
