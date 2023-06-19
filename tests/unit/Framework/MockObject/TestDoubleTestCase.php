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

use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\MockObject\ClassIsEnumerationException;
use PHPUnit\Framework\MockObject\ClassIsFinalException;
use PHPUnit\Framework\MockObject\ClassIsReadonlyException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AnInterface;
use PHPUnit\TestFixture\MockObject\AnotherInterface;
use PHPUnit\TestFixture\MockObject\Enumeration;
use PHPUnit\TestFixture\MockObject\ExtendableClass;
use PHPUnit\TestFixture\MockObject\FinalClass;
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
