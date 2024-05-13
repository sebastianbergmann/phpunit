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

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\Generator\ClassIsEnumerationException;
use PHPUnit\Framework\MockObject\Generator\ClassIsFinalException;
use PHPUnit\Framework\MockObject\Generator\UnknownTypeException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AnInterface;
use PHPUnit\TestFixture\MockObject\Enumeration;
use PHPUnit\TestFixture\MockObject\ExtendableClass;
use PHPUnit\TestFixture\MockObject\ExtendableReadonlyClass;
use PHPUnit\TestFixture\MockObject\FinalClass;

#[Group('test-doubles')]
#[Group('test-doubles/creation')]
#[Group('test-doubles/test-stub')]
#[Medium]
#[TestDox('createStub()')]
final class CreateStubTest extends TestCase
{
    public function testCreatesTestStubForInterface(): void
    {
        $double = $this->createStub(AnInterface::class);

        $this->assertInstanceOf(AnInterface::class, $double);
        $this->assertInstanceOf(Stub::class, $double);
    }

    public function testCreatesTestStubForExtendableClass(): void
    {
        $double = $this->createStub(ExtendableClass::class);

        $this->assertInstanceOf(ExtendableClass::class, $double);
        $this->assertInstanceOf(Stub::class, $double);
    }

    public function testCreatesTestStubForExtendableReadonlyClass(): void
    {
        $double = $this->createStub(ExtendableReadonlyClass::class);

        $this->assertInstanceOf(ExtendableReadonlyClass::class, $double);
        $this->assertInstanceOf(Stub::class, $double);
    }

    public function testReturnValueGenerationIsEnabledByDefault(): void
    {
        $double = $this->createStub(AnInterface::class);

        $this->assertFalse($double->doSomething());
    }

    public function testCannotCreateTestStubForFinalClass(): void
    {
        $this->expectException(ClassIsFinalException::class);

        $this->createStub(FinalClass::class);
    }

    public function testCannotCreateTestStubForEnumeration(): void
    {
        $this->expectException(ClassIsEnumerationException::class);

        $this->createStub(Enumeration::class);
    }

    public function testCannotCreateTestStubForUnknownType(): void
    {
        $this->expectException(UnknownTypeException::class);

        $this->createStub('this\does\not\exist');
    }
}
