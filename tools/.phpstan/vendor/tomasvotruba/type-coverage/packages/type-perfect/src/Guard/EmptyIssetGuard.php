<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Guard;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Type\TypeCombinator;

final class EmptyIssetGuard
{
    public function isLegal(Expr $expr, Scope $scope): bool
    {
        if ($expr instanceof ArrayDimFetch) {
            return true;
        }

        if (! $expr instanceof Variable) {
            return true;
        }

        if ($expr->name instanceof Expr) {
            return true;
        }

        if (! $scope->hasVariableType($expr->name)->yes()) {
            return true;
        }

        $varType = $scope->getNativeType($expr);
        $varType = TypeCombinator::removeNull($varType);
        return $varType->getObjectClassNames() === [];
    }
}
