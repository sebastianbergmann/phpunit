<?php

declare(strict_types=1);

namespace TomasVotruba\TypeCoverage\Collectors;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Reflection\ClassReflection;

/**
 * @see \TomasVotruba\TypeCoverage\Rules\ParamTypeCoverageRule
 */
final class ParamTypeDeclarationCollector implements Collector
{
    public function getNodeType(): string
    {
        return FunctionLike::class;
    }

    /**
     * @param FunctionLike $node
     * @return array{int, list<int>, string|null}|null
     */
    public function processNode(Node $node, Scope $scope): ?array
    {
        if ($this->shouldSkipFunctionLike($node)) {
            return null;
        }

        // skip methods inherited from a parent class or interface, as types are locked by LSP
        if ($node instanceof ClassMethod && $this->isGuardedByParentMethod($scope, $node)) {
            return null;
        }

        $missingTypeLines = [];
        $paramCount = count($node->getParams());

        foreach ($node->getParams() as $param) {
            if ($param->variadic) {
                // skip variadic
                --$paramCount;
                continue;
            }

            if ($param->type === null) {
                $missingTypeLines[] = $param->getLine();
            }
        }

        return [$paramCount, $missingTypeLines, $this->resolveTraitFilePath($scope)];
    }

    private function resolveTraitFilePath(Scope $scope): ?string
    {
        if (! $scope->isInTrait()) {
            return null;
        }

        return $scope->getTraitReflection()
            ->getFileName();
    }

    private function shouldSkipFunctionLike(FunctionLike $functionLike): bool
    {
        // nothing to analyse
        if ($functionLike->getParams() === []) {
            return true;
        }

        return $this->hasFunctionLikeCallableParam($functionLike);
    }

    private function isGuardedByParentMethod(Scope $scope, ClassMethod $classMethod): bool
    {
        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        $methodName = $classMethod->name->toString();

        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasMethod($methodName)) {
                return true;
            }
        }

        return array_any(
            $classReflection->getInterfaces(),
            fn (ClassReflection $classReflection): bool => $classReflection->hasMethod($methodName)
        );
    }

    private function hasFunctionLikeCallableParam(FunctionLike $functionLike): bool
    {
        // skip callable, can be anythings
        $docComment = $functionLike->getDocComment();
        if (! $docComment instanceof Doc) {
            return false;
        }

        $docCommentText = $docComment->getText();
        return str_contains($docCommentText, '@param callable');
    }
}
