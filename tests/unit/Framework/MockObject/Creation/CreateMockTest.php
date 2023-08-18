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
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\Generator\ClassIsEnumerationException;
use PHPUnit\Framework\MockObject\Generator\ClassIsFinalException;
use PHPUnit\Framework\MockObject\Generator\ClassIsReadonlyException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AnInterface;
use PHPUnit\TestFixture\MockObject\Enumeration;
use PHPUnit\TestFixture\MockObject\ExtendableClass;
use PHPUnit\TestFixture\MockObject\FinalClass;
use PHPUnit\TestFixture\MockObject\ReadonlyClass;

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
    }

    public function testCreatesMockObjectForExtendableClass(): void
    {
        $double = $this->createMock(ExtendableClass::class);

        $this->assertInstanceOf(ExtendableClass::class, $double);
        $this->assertInstanceOf(Stub::class, $double);
    }

    public function testCannotCreateMockObjectForFinalClass(): void
    {
        $this->expectException(ClassIsFinalException::class);

        $this->createMock(FinalClass::class);
    }

    #[RequiresPhp('8.2')]
    public function testCannotCreateMockObjectForReadonlyClass(): void
    {
        $this->expectException(ClassIsReadonlyException::class);

        $this->createMock(ReadonlyClass::class);
    }

    public function testCannotCreateMockObjectForEnumeration(): void
    {
        $this->expectException(ClassIsEnumerationException::class);

        $this->createMock(Enumeration::class);
    }
}
