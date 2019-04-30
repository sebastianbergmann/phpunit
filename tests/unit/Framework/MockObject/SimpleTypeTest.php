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
 * @covers \PHPUnit\Framework\MockObject\SimpleType
 */
class SimpleTypeTest extends TestCase
{
    /**
     * @dataProvider assignablePairs
     */
    public function testIsAssignable(Type $assignTo, Type $assignedType): void
    {
        $this->assertTrue($assignTo->isAssignable($assignedType));
    }

    public function assignablePairs(): array
    {
        return [
            'nullable to notNullable'    => [new SimpleType('int', false), new SimpleType('int', true)],
            'notNullable to nullable'    => [new SimpleType('int', true), new SimpleType('int', false)],
            'nullable to nullable'       => [new SimpleType('int', true), new SimpleType('int', true)],
            'notNullable to notNullable' => [new SimpleType('int', false), new SimpleType('int', false)],
            'null to notNullable'        => [new SimpleType('int', true), new NullType()],
        ];
    }

    /**
     * @dataProvider notAssignablePairs
     */
    public function testIsNotAssignable(Type $assignTo, Type $assignedType): void
    {
        $this->assertFalse($assignTo->isAssignable($assignedType));
    }

    public function notAssignablePairs(): array
    {
        return [
            'null to notNullable' => [new SimpleType('int', false), new NullType()],
            'int to boolean'      => [new SimpleType('boolean', false), new SimpleType('int', false)],
            'object'              => [new SimpleType('boolean', false), new ObjectType(TypeName::fromQualifiedName(\stdClass::class), true)],
            'unknown type'        => [new SimpleType('boolean', false), new UnknownType()],
            'void'                => [new SimpleType('boolean', false), new VoidType()],
        ];
    }

    /**
     * @dataProvider returnTypes
     */
    public function testReturnTypeDeclaration(Type $type, string $returnType): void
    {
        $this->assertEquals($type->getReturnTypeDeclaration(), $returnType);
    }

    public function returnTypes(): array
    {
        return [
            '[]'      => [new SimpleType('[]', false), ': array'],
            'array'   => [new SimpleType('array', false), ': array'],
            '?array'  => [new SimpleType('array', true), ': ?array'],
            'boolean' => [new SimpleType('boolean', false), ': bool'],
            'real'    => [new SimpleType('real', false), ': float'],
            'double'  => [new SimpleType('double', false), ': float'],
            'integer' => [new SimpleType('integer', false), ': int'],
        ];
    }
}
