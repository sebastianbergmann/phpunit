<?php declare(strict_types=1);

namespace Composer\Pcre\PHPStan;

use Composer\Pcre\Preg;
use Composer\Pcre\Regex;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Native\NativeParameterReflection;
use PHPStan\Reflection\ParameterReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Php\RegexArrayShapeMatcher;
use PHPStan\Type\StaticMethodParameterClosureTypeExtension;
use PHPStan\Type\StringType;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\Type;

final class PregReplaceCallbackClosureTypeExtension implements StaticMethodParameterClosureTypeExtension
{
    /**
     * @var RegexArrayShapeMatcher
     */
    private $regexShapeMatcher;

    public function __construct(RegexArrayShapeMatcher $regexShapeMatcher)
    {
        $this->regexShapeMatcher = $regexShapeMatcher;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection, ParameterReflection $parameter): bool
    {
        return in_array($methodReflection->getDeclaringClass()->getName(), [Preg::class, Regex::class], true)
            && in_array($methodReflection->getName(), ['replaceCallback', 'replaceCallbackStrictGroups'], true)
            && $parameter->getName() === 'replacement';
    }

    public function getTypeFromStaticMethodCall(MethodReflection $methodReflection, StaticCall $methodCall, ParameterReflection $parameter, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();
        $patternArg = $args[0] ?? null;
        $flagsArg = $args[5] ?? null;

        if (
            $patternArg === null
        ) {
            return null;
        }

        $flagsType = PregMatchFlags::getType($flagsArg, $scope);

        $matchesType = $this->regexShapeMatcher->matchExpr($patternArg->value, $flagsType, TrinaryLogic::createYes(), $scope);
        if ($matchesType === null) {
            return null;
        }

        if ($methodReflection->getName() === 'replaceCallbackStrictGroups' && count($matchesType->getConstantArrays()) === 1) {
            $matchesType = $matchesType->getConstantArrays()[0];
            $matchesType = new ConstantArrayType(
                $matchesType->getKeyTypes(),
                array_map(static function (Type $valueType): Type {
                    if (count($valueType->getConstantArrays()) === 1) {
                        $valueTypeArray = $valueType->getConstantArrays()[0];
                        return new ConstantArrayType(
                            $valueTypeArray->getKeyTypes(),
                            array_map(static function (Type $valueType): Type {
                                return TypeCombinator::removeNull($valueType);
                            }, $valueTypeArray->getValueTypes()),
                            $valueTypeArray->getNextAutoIndexes(),
                            [],
                            $valueTypeArray->isList()
                        );
                    }
                    return TypeCombinator::removeNull($valueType);
                }, $matchesType->getValueTypes()),
                $matchesType->getNextAutoIndexes(),
                [],
                $matchesType->isList()
            );
        }

        return new ClosureType(
            [
                new NativeParameterReflection($parameter->getName(), $parameter->isOptional(), $matchesType, $parameter->passedByReference(), $parameter->isVariadic(), $parameter->getDefaultValue()),
            ],
            new StringType()
        );
    }
}
