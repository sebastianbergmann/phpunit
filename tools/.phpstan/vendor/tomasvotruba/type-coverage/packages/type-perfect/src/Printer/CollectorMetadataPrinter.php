<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Printer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType as NodeIntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\UnionType;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\IntegerRangeType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\UnionType as PHPStanUnionType;
use PHPStan\Type\VerbosityLevel;
use Rector\TypePerfect\Enum\Types\ResolvedTypes;

final readonly class CollectorMetadataPrinter
{
    private Standard $standard;

    public function __construct(
    ) {
        $this->standard = new Standard();
    }

    public function printArgTypesAsString(
        MethodCall $methodCall,
        ExtendedMethodReflection $extendedMethodReflection,
        Scope $scope
    ): string {
        $parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
            $scope,
            $methodCall->getArgs(),
            $extendedMethodReflection->getVariants(),
            $extendedMethodReflection->getNamedArgumentsVariants()
        );
        $parameters = $parametersAcceptor->getParameters();

        $stringArgTypes = [];
        foreach ($methodCall->getArgs() as $i => $arg) {
            $argType = $scope->getType($arg->value);

            // we have no idea, nothing we can do
            if ($argType instanceof MixedType) {
                return ResolvedTypes::UNKNOWN_TYPES;
            }

            if ($argType instanceof IntersectionType) {
                return ResolvedTypes::UNKNOWN_TYPES;
            }

            if ($argType instanceof GenericObjectType) {
                return ResolvedTypes::UNKNOWN_TYPES;
            }

            if ($argType instanceof PHPStanUnionType) {
                return ResolvedTypes::UNKNOWN_TYPES;
            }

            if ($argType instanceof ThisType) {
                $argType = new ObjectType($argType->getClassName());
            }

            if (array_key_exists($i, $parameters)) {
                $defaultValueType = $parameters[$i]->getDefaultValue();
                if ($defaultValueType instanceof Type) {
                    $argType = TypeCombinator::union($argType, $defaultValueType);
                }
            }

            $printedArgType = $this->printTypeToString($argType);
            $printedArgType = $this->normalizeDateTime($printedArgType);

            $stringArgTypes[] = $printedArgType;
        }

        return implode('|', $stringArgTypes);
    }

    public function printParamTypesToString(
        ClassMethod $classMethod,
        ClassReflection $classReflection,
        Scope $scope
    ): string {
        $className = $classReflection->getName();

        $parametersReflection = [];
        if ($classReflection->hasMethod($classMethod->name->name)) {
            $methodReflection = $classReflection->getMethod($classMethod->name->name, $scope);
            $variants = $methodReflection->getVariants();
            if (count($variants) === 1) {
                $parametersReflection = $variants[0]->getParameters();
            }
        }

        $printedParamTypes = [];
        foreach ($classMethod->params as $i => $param) {
            if ($param->type === null) {
                $printedParamTypes[] = '';
                continue;
            }

            $phpdocType = null;
            if (array_key_exists($i, $parametersReflection)) {
                $paramphpdocType = $parametersReflection[$i]->getPhpDocType();
                if (! $paramphpdocType instanceof MixedType) {
                    $phpdocType = $paramphpdocType;
                }
            }

            if ($phpdocType instanceof Type) {
                $printedParamType = $this->printTypeToString($phpdocType);
            } else {
                $paramType = $this->transformSelfToClassName($param->type, $className);

                if ($paramType instanceof NullableType) {
                    // unite to phpstan type
                    $paramType = new UnionType([$paramType->type, new Identifier('null')]);
                }

                if ($paramType instanceof UnionType || $paramType instanceof NodeIntersectionType) {
                    $paramType = $this->resolveSortedTypes($paramType, $className);
                }

                $printedParamType = $this->standard->prettyPrint([$paramType]);
                $printedParamType = str_replace('\Closure', 'callable', $printedParamType);
                $printedParamType = ltrim($printedParamType, '\\');
                $printedParamType = str_replace('|\\', '|', $printedParamType);
            }

            // to avoid DateTime vs DateTimeImmutable vs DateTimeInterface conflicts
            $printedParamType = $this->normalizeDateTime($printedParamType);

            $printedParamTypes[] = $printedParamType;
        }

        return implode('|', $printedParamTypes);
    }

    private function transformSelfToClassName(Node $node, ?string $className): Node
    {
        if (! $node instanceof Name || $className === null) {
            return $node;
        }

        if ($node->toString() !== 'self') {
            return $node;
        }

        return new FullyQualified($className);
    }

    private function resolveSortedTypes(
        UnionType|NodeIntersectionType $paramType,
        ?string $className
    ): UnionType|NodeIntersectionType {
        $typeNames = [];

        foreach ($paramType->types as $type) {
            if ($type instanceof NodeIntersectionType) {
                foreach ($type->types as $intersectionType) {
                    /** @var Identifier|Name $intersectionType */
                    $intersectionType = $this->transformSelfToClassName($intersectionType, $className);
                    $typeNames[] = (string) $intersectionType;
                }

                continue;
            }

            /** @var Identifier|Name $type */
            $type = $this->transformSelfToClassName($type, $className);
            $typeNames[] = (string) $type;
        }

        sort($typeNames);

        $types = [];
        foreach ($typeNames as $typeName) {
            $types[] = new Identifier($typeName);
        }

        if ($paramType instanceof NodeIntersectionType) {
            return new NodeIntersectionType($types);
        }

        return new UnionType($types);
    }

    private function printTypeToString(Type $type): string
    {
        if ($type->isClassString()->yes()) {
            return 'string';
        }

        if ($type->isArray()->yes()) {
            return 'array';
        }

        if ($type->isBoolean()->yes()) {
            return 'bool';
        }

        if ($type instanceof IntegerRangeType) {
            return 'int';
        }

        if ($type instanceof ClosureType) {
            return 'callable';
        }

        if (count($type->getEnumCases()) === 1) {
            return $type->getEnumCases()[0]
                ->getClassName();
        }

        return $type->describe(VerbosityLevel::typeOnly());
    }

    private function normalizeDateTime(string $printedType): string
    {
        if ($printedType === 'DateTimeImmutable') {
            return 'DateTimeInterface';
        }

        if ($printedType === 'DateTime') {
            return 'DateTimeInterface';
        }

        return $printedType;
    }
}
