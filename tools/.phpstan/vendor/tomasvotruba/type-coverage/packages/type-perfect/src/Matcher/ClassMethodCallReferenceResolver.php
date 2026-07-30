<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Matcher;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Node\MethodCallableNode;
use PHPStan\Type\ThisType;
use PHPStan\Type\TypeCombinator;
use Rector\TypePerfect\ValueObject\MethodCallReference;

final class ClassMethodCallReferenceResolver
{
    public function resolve(
        MethodCall|MethodCallableNode $methodCallOrMethodCallable,
        Scope $scope,
        bool $allowThisType
    ): ?MethodCallReference {
        if ($methodCallOrMethodCallable instanceof MethodCallableNode) {
            $methodName = $methodCallOrMethodCallable->getName();
            $variable = $methodCallOrMethodCallable->getVar();
        } else {
            $methodName = $methodCallOrMethodCallable->name;
            $variable = $methodCallOrMethodCallable->var;
        }

        if ($methodName instanceof Expr) {
            return null;
        }

        $callerType = $scope->getType($variable);

        // remove optional nullable type
        if (TypeCombinator::containsNull($callerType)) {
            $callerType = TypeCombinator::removeNull($callerType);
        }

        if (! $allowThisType && $callerType instanceof ThisType) {
            return null;
        }

        if (count($callerType->getObjectClassNames()) !== 1) {
            return null;
        }

        // move to the class where method is defined, e.g. parent class defines the method, so it should be checked there
        $className = $callerType->getObjectClassNames()[0];
        $methodNameString = $methodName->toString();

        return new MethodCallReference($className, $methodNameString);
    }
}
