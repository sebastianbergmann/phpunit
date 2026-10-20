<?php declare(strict_types=1);

namespace Composer\Pcre\PHPStan;

use PHPStan\Analyser\Scope;
use PHPStan\Type\ArrayType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\Type;
use PhpParser\Node\Arg;
use PHPStan\Type\Php\RegexArrayShapeMatcher;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\UnionType;

final class PregMatchFlags
{
    static public function getType(?Arg $flagsArg, Scope $scope): ?Type
    {
        if ($flagsArg === null) {
            return new ConstantIntegerType(PREG_UNMATCHED_AS_NULL);
        }

        $flagsType = $scope->getType($flagsArg->value);

        $constantScalars = $flagsType->getConstantScalarValues();
        if ($constantScalars === []) {
            return null;
        }

        $internalFlagsTypes = [];
        foreach ($flagsType->getConstantScalarValues() as $constantScalarValue) {
            if (!is_int($constantScalarValue)) {
                return null;
            }

            $internalFlagsTypes[] = new ConstantIntegerType($constantScalarValue | PREG_UNMATCHED_AS_NULL);
        }
        return TypeCombinator::union(...$internalFlagsTypes);
    }

    static public function removeNullFromMatches(Type $matchesType): Type
    {
        return TypeTraverser::map($matchesType, static function (Type $type, callable $traverse): Type {
            if ($type instanceof UnionType || $type instanceof IntersectionType) {
                return $traverse($type);
            }

            if ($type instanceof ConstantArrayType) {
                return new ConstantArrayType(
                    $type->getKeyTypes(),
                    array_map(static function (Type $valueType) use ($traverse): Type {
                        return $traverse($valueType);
                    }, $type->getValueTypes()),
                    $type->getNextAutoIndexes(),
                    [],
                    $type->isList()
                );
            }

            if ($type instanceof ArrayType) {
                return new ArrayType($type->getKeyType(), $traverse($type->getItemType()));
            }

            return TypeCombinator::removeNull($type);
        });
    }

}
