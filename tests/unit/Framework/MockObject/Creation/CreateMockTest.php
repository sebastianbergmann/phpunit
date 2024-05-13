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
#[Group('test-doubles/mock-object')]
#[Medium]
#[TestDox('createMock()')]
final class CreateMockTest extends TestCase
{
    public function testCreatesMockObjectForInterface(): void
    {
        $double = $this->createMock(AnInterface::class);

        $this->assertInstanceOf(AnInterface::class, $double);
        $this->assertInstanceOf(Stub::class, $double);
        $this->assertInstanceOf(MockObject::class, $double);
    }

    public function testCreatesMockObjectForExtendableClass(): void
    {
        $double = $this->createMock(ExtendableClass::class);

        $this->assertInstanceOf(ExtendableClass::class, $double);
        $this->assertInstanceOf(Stub::class, $double);
        $this->assertInstanceOf(MockObject::class, $double);
    }

    public function testCreatesMockObjectForExtendableReadonlyClass(): void
    {
        $double = $this->createMock(ExtendableReadonlyClass::class);

        $this->assertInstanceOf(ExtendableReadonlyClass::class, $double);
        $this->assertInstanceOf(Stub::class, $double);
        $this->assertInstanceOf(MockObject::class, $double);
    }

    public function testReturnValueGenerationIsEnabledByDefault(): void
    {
        $double = $this->createMock(AnInterface::class);

        $this->assertFalse($double->doSomething());
    }

    public function testCannotCreateMockObjectForFinalClass(): void
    {
        $this->expectException(ClassIsFinalException::class);

        $this->createMock(FinalClass::class);
    }

    public function testCannotCreateMockObjectForEnumeration(): void
    {
        $this->expectException(ClassIsEnumerationException::class);

        $this->createMock(Enumeration::class);
    }

    public function testCannotCreateMockObjectForUnknownType(): void
    {
        $this->expectException(UnknownTypeException::class);

        $this->createMock('this\does\not\exist');
    }
}
