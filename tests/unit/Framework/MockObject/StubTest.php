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
use PHPUnit\TestFixture\MockObject\AClass;
use PHPUnit\TestFixture\MockObject\AnInterface;
use PHPUnit\TestFixture\MockObject\AnotherInterface;
use PHPUnit\TestFixture\MockObject\Enumeration;
use PHPUnit\TestFixture\MockObject\FinalClass;
use PHPUnit\TestFixture\MockObject\ReadonlyClass;

#[Small]
final class StubTest extends TestCase
{
    public function testCanBeCreatedForInterface(): void
    {
        $stub = $this->createStub(AnInterface::class);

        $this->assertInstanceOf(AnInterface::class, $stub);
        $this->assertInstanceOf(Stub::class, $stub);
    }

    public function testCanBeCreatedForIntersectionOfInterfaces(): void
    {
        $stub = $this->createStubForIntersectionOfInterfaces([AnInterface::class, AnotherInterface::class]);

        $this->assertInstanceOf(AnInterface::class, $stub);
        $this->assertInstanceOf(AnotherInterface::class, $stub);
        $this->assertInstanceOf(Stub::class, $stub);
    }

    public function testCanBeCreatedForClassThatCanBeExtended(): void
    {
        $stub = $this->createStub(AClass::class);

        $this->assertInstanceOf(AClass::class, $stub);
        $this->assertInstanceOf(Stub::class, $stub);
    }

    public function testCannotBeCreatedForFinalClass(): void
    {
        $this->expectException(ClassIsFinalException::class);

        $this->createStub(FinalClass::class);
    }

    #[RequiresPhp('8.2')]
    public function testCannotBeCreatedForReadonlyClass(): void
    {
        $this->expectException(ClassIsReadonlyException::class);

        $this->createStub(ReadonlyClass::class);
    }

    public function testCannotBeCreatedForEnumeration(): void
    {
        $this->expectException(ClassIsEnumerationException::class);

        $this->createStub(Enumeration::class);
    }
}
