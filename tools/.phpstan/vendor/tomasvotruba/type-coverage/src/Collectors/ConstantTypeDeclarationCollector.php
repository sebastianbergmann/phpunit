<?php

declare(strict_types=1);

namespace TomasVotruba\TypeCoverage\Collectors;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use PHPStan\Analyser\Scope;
use PHPStan\Collectors\Collector;
use PHPStan\Node\ClassConstantsNode;
use PHPStan\Reflection\ClassReflection;

/**
 * @see \TomasVotruba\TypeCoverage\Rules\ConstantTypeCoverageRule
 */
final class ConstantTypeDeclarationCollector implements Collector
{
    /**
     * @return class-string<Node>
     */
    public function getNodeType(): string
    {
        return ClassConstantsNode::class;
    }

    /**
     * @param ClassConstantsNode $node
     * @return array<int, int|list<(int | int<1, max>)>>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        // enable only on PHP 8.3+
        if (PHP_VERSION_ID < 80300) {
            return [0, []];
        }

        $constantCount = count($node->getConstants());

        $missingTypeLines = [];

        foreach ($node->getConstants() as $classConst) {
            // blocked by parent type
            if ($this->isGuardedByParentClassConstant($scope, $classConst)) {
                continue;
            }

            // already typed
            if ($classConst->type instanceof Node) {
                continue;
            }

            // give useful context
            $missingTypeLines[] = $classConst->getLine();
        }

        return [$constantCount, $missingTypeLines];
    }

    private function isGuardedByParentClassConstant(Scope $scope, ClassConst $classConst): bool
    {
        $constName = $classConst->consts[0]->name->toString();

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return false;
        }

        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasConstant($constName)) {
                return true;
            }
        }

        return false;
    }
}
