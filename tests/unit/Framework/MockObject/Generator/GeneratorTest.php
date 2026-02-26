<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\MockObject\Generator;

use Iterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\ExtendableClass;
use PHPUnit\TestFixture\MockObject\ExtendableClassWithConstructorArguments;
use PHPUnit\TestFixture\MockObject\InterfaceExtendingThrowable;
use PHPUnit\TestFixture\MockObject\InterfaceExtendingTraversable;
use Traversable;

#[CoversClass(Generator::class)]
#[Group('test-doubles')]
#[Small]
final class GeneratorTest extends TestCase
{
    public function testReplacesTraversableWithIterator(): void
    {
        $double = (new Generator)->testDouble(Traversable::class, false);

        $this->assertInstanceOf(Stub::class, $double);
    }

    public function testHandlesLeadingBackslashInType(): void
    {
        $double = (new Generator)->generate('\PHPUnit\TestFixture\MockObject\AnInterface', false);

        $this->assertInstanceOf(DoubledClass::class, $double);
    }

    public function testThrowsExceptionWhenMockClassNameAlreadyExists(): void
    {
        $this->expectException(NameAlreadyInUseException::class);

        (new Generator)->testDouble(ExtendableClass::class, false, [], [], 'stdClass');
    }

    public function testAddsIteratorMethodsWhenMockingTraversableInterface(): void
    {
        $double = (new Generator)->testDouble(InterfaceExtendingTraversable::class, false);

        $this->assertInstanceOf(Iterator::class, $double);
    }

    public function testIgnoresPrivateMethodWhenExplicitlyListed(): void
    {
        $double = (new Generator)->testDouble(ExtendableClass::class, false, ['privateMethod']);

        $this->assertInstanceOf(Stub::class, $double);
    }

    public function testCallsOriginalConstructorWithArguments(): void
    {
        $double = $this->getMockBuilder(ExtendableClassWithConstructorArguments::class)
            ->enableOriginalConstructor()
            ->setConstructorArgs(['hello'])
            ->onlyMethods([])
            ->getMock();

        $this->assertSame('hello', $double->value());
    }

    public function testMocksInterfaceExtendingThrowable(): void
    {
        $double = (new Generator)->testDouble(InterfaceExtendingThrowable::class, false);

        $this->assertInstanceOf(InterfaceExtendingThrowable::class, $double);
    }
}
