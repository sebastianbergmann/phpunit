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

use PHPUnit\Framework\TestCase;

/**
 * @covers \PHPUnit\Framework\MockObject\VoidType
 */
final class VoidTypeTest extends TestCase
{
    /**
     * @dataProvider assignableTypes
     */
    public function testIsAssignable(Type $assignableType): void
    {
        $void = new VoidType();
        $this->assertTrue($void->isAssignable($assignableType));
    }

    public function assignableTypes(): array
    {
        return [
            [new VoidType()],
        ];
    }

    /**
     * @dataProvider notAssignableTypes
     */
    public function testIsNotAssignable(Type $assignableType): void
    {
        $void = new VoidType();
        $this->assertFalse($void->isAssignable($assignableType));
    }

    public function notAssignableTypes(): array
    {
        return [
            [new SimpleType('int', false)],
            [new SimpleType('int', true)],
            [new ObjectType(TypeName::fromQualifiedName(self::class), false)],
            [new ObjectType(TypeName::fromQualifiedName(self::class), true)],
            [new UnknownType()],
        ];
    }

    public function testNotAllowNull(): void
    {
        $type = new VoidType();
        $this->assertFalse($type->allowsNull());
    }

    public function testReturnTypeDeclaration(): void
    {
        $type = new VoidType();
        $this->assertEquals(': void', $type->getReturnTypeDeclaration());
    }
}
