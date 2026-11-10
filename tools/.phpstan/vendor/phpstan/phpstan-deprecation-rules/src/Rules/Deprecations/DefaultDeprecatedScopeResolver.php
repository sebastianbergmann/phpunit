<?php declare(strict_types = 1);

namespace PHPStan\Rules\Deprecations;

use PHPStan\Analyser\Scope;

final class DefaultDeprecatedScopeResolver implements DeprecatedScopeResolver
{

	public function isScopeDeprecated(Scope $scope): bool
	{
		$class = $scope->getClassReflection();
		if ($class !== null && $class->isDeprecated()) {
			return true;
		}

		$trait = $scope->getTraitReflection();
		if ($trait !== null && $trait->isDeprecated()) {
			return true;
		}

		$function = $scope->getFunction();
		if ($function !== null && $function->isDeprecated()->yes()) {
			return true;
		}

		return false;
	}

}
