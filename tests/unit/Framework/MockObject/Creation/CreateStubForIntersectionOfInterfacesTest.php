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
use PHPUnit\Framework\MockObject\Generator\RuntimeException as GeneratorRuntimeException;
use PHPUnit\Framework\MockObject\Generator\UnknownTypeException;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\AnInterface;
use PHPUnit\TestFixture\MockObject\AnotherInterface;
use PHPUnit\TestFixture\MockObject\AnotherInterfaceThatDoesSomething;

#[Group('test-doubles')]
#[Group('test-doubles/creation')]
#[Group('test-doubles/test-stub')]
#[Medium]
#[TestDox('createStubForIntersectionOfInterfaces()')]
final class CreateStubForIntersectionOfInterfacesTest extends TestCase
{
    public function testCreatesTestStubForIntersectionOfInterfaces(): void
    {
        $double = $this->createStubForIntersectionOfInterfaces([AnInterface::class, AnotherInterface::class]);

        $this->assertInstanceOf(AnInterface::class, $double);
        $this->assertInstanceOf(AnotherInterface::class, $double);
        $this->assertInstanceOf(Stub::class, $double);

        $double->method('doSomething')->willReturn(true);
        $double->method('doSomethingElse')->willReturn(true);

        $this->assertTrue($double->doSomething());
        $this->assertTrue($double->doSomethingElse());
    }

    public function testReturnValueGenerationIsEnabledByDefault(): void
    {
        $double = $this->createStubForIntersectionOfInterfaces([AnInterface::class, AnotherInterface::class]);

        $this->assertFalse($double->doSomething());
        $this->assertNull($double->doSomethingElse());
    }

    public function testCannotCreateTestStubForIntersectionOfInterfacesWhenLessThanTwoInterfacesAreSpecified(): void
    {
        $this->expectException(GeneratorRuntimeException::class);
        $this->expectExceptionMessage('At least two interfaces must be specified');

        $this->createStubForIntersectionOfInterfaces([AnInterface::class]);
    }

    public function testCannotCreateTestStubForIntersectionOfUnknownInterfaces(): void
    {
        $this->expectException(UnknownTypeException::class);

        $this->createStubForIntersectionOfInterfaces(['DoesNotExist', 'DoesNotExist']);
    }

    public function testCannotCreateTestStubForIntersectionOfInterfacesThatDeclareTheSameMethod(): void
    {
        $this->expectException(GeneratorRuntimeException::class);
        $this->expectExceptionMessage('Interfaces must not declare the same method');

        $this->createStubForIntersectionOfInterfaces([AnInterface::class, AnotherInterfaceThatDoesSomething::class]);
    }
}
