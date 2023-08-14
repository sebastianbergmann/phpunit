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

use function md5;
use function mt_rand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Medium;
use PHPUnit\Framework\TestCase;
use PHPUnit\TestFixture\MockObject\ExtendableClass;
use PHPUnit\TestFixture\MockObject\InterfaceWithReturnTypeDeclaration;

#[CoversClass(MockBuilder::class)]
#[CoversClass(CannotUseAddMethodsException::class)]
#[Group('test-doubles')]
#[Group('test-doubles/creation')]
#[Group('test-doubles/mock-object')]
#[Medium]
final class MockBuilderTest extends TestCase
{
    public function testCanCreateMockObjectWithSpecifiedClassName(): void
    {
        $className = 'random_' . md5((string) mt_rand());

        $double = $this->getMockBuilder(InterfaceWithReturnTypeDeclaration::class)
            ->setMockClassName($className)
            ->getMock();

        $this->assertSame($className, $double::class);
    }

    public function testCanCreateMockObjectForExtendableClassWhileAddingMethodsToIt(): void
    {
        $double = $this->getMockBuilder(ExtendableClass::class)
            ->addMethods(['additionalMethod'])
            ->getMock();

        $value = 'value';

        $double->method('additionalMethod')->willReturn($value);

        $this->assertSame($value, $double->additionalMethod());
    }

    public function testCannotCreateMockObjectForExtendableClassAddingMethodsToItThatItAlreadyHas(): void
    {
        $this->expectException(CannotUseAddMethodsException::class);

        $this->getMockBuilder(ExtendableClass::class)
            ->addMethods(['doSomething'])
            ->getMock();
    }
}
