<?php

declare(strict_types=1);

namespace Rector\TypePerfect\Reflection;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;

final class MethodNodeAnalyser
{
    public function hasParentVendorLock(Scope $scope, string $methodName): bool
    {
        return $this->matchFirstParentClassMethod($scope, $methodName) instanceof MethodReflection;
    }

    public function matchFirstParentClassMethod(Scope $scope, string $methodName): ?MethodReflection
    {
        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        // the classes have highter priority, e.g. priority in class covariance
        foreach ($classReflection->getParents() as $parentClassReflection) {
            if ($parentClassReflection->hasNativeMethod($methodName)) {
                return $parentClassReflection->getNativeMethod($methodName);
            }
        }

        foreach ($classReflection->getAncestors() as $ancestorClassReflection) {
            if ($classReflection === $ancestorClassReflection) {
                continue;
            }

            if (! $ancestorClassReflection->hasNativeMethod($methodName)) {
                continue;
            }

            return $ancestorClassReflection->getNativeMethod($methodName);
        }

        return null;
    }
}
