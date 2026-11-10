<?php declare(strict_types = 1);

namespace PHPStan\Rules\Deprecations;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Rules\RestrictedUsage\RestrictedFunctionUsageExtension;
use PHPStan\Rules\RestrictedUsage\RestrictedUsage;
use function sprintf;

class RestrictedDeprecatedFunctionUsageExtension implements RestrictedFunctionUsageExtension
{

	private DeprecatedScopeHelper $deprecatedScopeHelper;

	public function __construct(DeprecatedScopeHelper $deprecatedScopeHelper)
	{
		$this->deprecatedScopeHelper = $deprecatedScopeHelper;
	}

	public function isRestrictedFunctionUsage(
		FunctionReflection $functionReflection,
		Scope $scope
	): ?RestrictedUsage
	{
		if ($this->deprecatedScopeHelper->isScopeDeprecated($scope)) {
			return null;
		}

		if (!$functionReflection->isDeprecated()->yes()) {
			return null;
		}

		$description = $functionReflection->getDeprecatedDescription();
		if ($description === null) {
			return RestrictedUsage::create(
				sprintf(
					'Call to deprecated function %s().',
					$functionReflection->getName(),
				),
				'function.deprecated',
			);
		}

		return RestrictedUsage::create(
			sprintf(
				"Call to deprecated function %s():\n%s",
				$functionReflection->getName(),
				$description,
			),
			'function.deprecated',
		);
	}

}
