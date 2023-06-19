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

use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\ExtendableClass;
use PHPUnit\TestFixture\MockObject\AnInterface;
use PHPUnit\TestFixture\MockObject\AnotherInterface;
use PHPUnit\TestFixture\MockObject\Enumeration;
use PHPUnit\TestFixture\MockObject\FinalClass;
use PHPUnit\TestFixture\MockObject\ReadonlyClass;

#[Small]
final class MockObjectTest extends TestCase
{
    public function testCanBeCreatedForInterface(): void
    {
        $mock = $this->createMock(AnInterface::class);

        $this->assertInstanceOf(AnInterface::class, $mock);
        $this->assertInstanceOf(MockObject::class, $mock);
    }

    public function testCanBeCreatedForIntersectionOfInterfaces(): void
    {
        $mock = $this->createMockForIntersectionOfInterfaces([AnInterface::class, AnotherInterface::class]);

        $this->assertInstanceOf(AnInterface::class, $mock);
        $this->assertInstanceOf(AnotherInterface::class, $mock);
        $this->assertInstanceOf(MockObject::class, $mock);
    }

    public function testCanBeCreatedForClassThatCanBeExtended(): void
    {
        $mock = $this->createMock(ExtendableClass::class);

        $this->assertInstanceOf(ExtendableClass::class, $mock);
        $this->assertInstanceOf(MockObject::class, $mock);
    }

    public function testCannotBeCreatedForFinalClass(): void
    {
        $this->expectException(ClassIsFinalException::class);

        $this->createMock(FinalClass::class);
    }

    #[RequiresPhp('8.2')]
    public function testCannotBeCreatedForReadonlyClass(): void
    {
        $this->expectException(ClassIsReadonlyException::class);

        $this->createMock(ReadonlyClass::class);
    }

    public function testCannotBeCreatedForEnumeration(): void
    {
        $this->expectException(ClassIsEnumerationException::class);

        $this->createMock(Enumeration::class);
    }
}
