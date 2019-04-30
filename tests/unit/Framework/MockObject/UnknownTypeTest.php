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
 * @covers \PHPUnit\Framework\MockObject\UnknownType
 */
final class UnknownTypeTest extends TestCase
{
    /**
     * @dataProvider assignableTypes
     */
    public function testIsAssignable(Type $assignableType): void
    {
        $unknownType = new UnknownType();
        $this->assertTrue($unknownType->isAssignable($assignableType));
    }

    public function assignableTypes(): array
    {
        return [
            [new SimpleType('int', false)],
            [new SimpleType('int', true)],
            [new VoidType()],
            [new ObjectType(TypeName::fromQualifiedName(self::class), false)],
            [new ObjectType(TypeName::fromQualifiedName(self::class), true)],
            [new UnknownType()],
        ];
    }

    public function testAllowsNull(): void
    {
        $type = new UnknownType();
        $this->assertTrue($type->allowsNull());
    }

    public function testReturnTypeDeclaration(): void
    {
        $type = new UnknownType();
        $this->assertEquals('', $type->getReturnTypeDeclaration());
    }
}
