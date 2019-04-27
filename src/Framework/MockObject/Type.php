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


abstract class Type
{

    public static function fromValue($value, bool $allowsNull): Type
    {
        $type = gettype($value);
        switch ($type) {
            case 'object':
                return self::fromName(get_class($value), $allowsNull);
            default:
                return new SimpleType($type, $allowsNull);
        }
    }

    public static function fromName(?string $typeName, bool $allowsNull): Type
    {
        switch (mb_strtolower($typeName)) {
            case null:
            case 'unknown type':
                return new UnknownType();
            case 'object':
            case 'boolean':
            case 'bool':
            case 'integer':
            case 'int':
            case 'real':
            case 'double':
            case 'float':
            case 'string':
            case 'array':
            case 'resource':
            case 'resource (closed)':
                return new SimpleType($typeName, $allowsNull);
            default:
                return new ObjectType(TypeName::fromQualifiedName($typeName), $allowsNull);
        }
    }

    abstract public function isAssignable(Type $other): bool;

    abstract public function getReturnTypeDeclaration(): string;

    abstract public function allowsNull(): bool;

}
