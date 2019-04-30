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
 * @covers \PHPUnit\Framework\MockObject\Type
 */
final class TypeTest extends TestCase
{
    /**
     * @dataProvider valuesToNullableType
     */
    public function testTypeMappingFromValue($value, bool $allowsNull, Type $expectedType): void
    {
        $this->assertEquals($expectedType, Type::fromValue($value, $allowsNull));
    }

    public function valuesToNullableType(): array
    {
        return [
            '?null'    => [null, true, new NullType()],
            'null'     => [null, false, new NullType()],
            '?integer' => [1, true, new SimpleType('int', true)],
            'integer'  => [1, false, new SimpleType('int', false)],
            '?boolean' => [true, true, new SimpleType('bool', true)],
            'boolean'  => [true, false, new SimpleType('bool', false)],
            '?object'  => [new \stdClass(), true, new ObjectType(TypeName::fromQualifiedName(\stdClass::class), true)],
            'object'   => [new \stdClass(), false, new ObjectType(TypeName::fromQualifiedName(\stdClass::class), false)],
        ];
    }

    /**
     * @dataProvider namesToTypes
     */
    public function testTypeMappingFromName(string $typeName, bool $allowsNull, $expectedType): void
    {
        $this->assertEquals($expectedType, Type::fromName($typeName, $allowsNull));
    }

    public function namesToTypes(): array
    {
        return [
            '?void'             => ['void', true, new VoidType()],
            'void'              => ['void', false, new VoidType()],
            '?null'             => ['null', true, new NullType()],
            'null'              => ['null', true, new NullType()],
            '?int'              => ['int', true, new SimpleType('int', true)],
            '?integer'          => ['integer', true, new SimpleType('int', true)],
            'int'               => ['int', false, new SimpleType('int', false)],
            'bool'              => ['bool', false, new SimpleType('bool', false)],
            'boolean'           => ['boolean', false, new SimpleType('bool', false)],
            'object'            => ['object', false, new SimpleType('object', false)],
            'real'              => ['real', false, new SimpleType('float', false)],
            'double'            => ['double', false, new SimpleType('float', false)],
            'float'             => ['float', false, new SimpleType('float', false)],
            'string'            => ['string', false, new SimpleType('string', false)],
            'array'             => ['array', false, new SimpleType('array', false)],
            'resource'          => ['resource', false, new SimpleType('resource', false)],
            'resource (closed)' => ['resource (closed)', false, new SimpleType('resource (closed)', false)],
            'unknown type'      => ['unknown type', false, new UnknownType()],
            '?object'           => [\stdClass::class, true, new ObjectType(TypeName::fromQualifiedName(\stdClass::class), true)],
            'classname'         => [\stdClass::class, false, new ObjectType(TypeName::fromQualifiedName(\stdClass::class), false)],
        ];
    }
}
