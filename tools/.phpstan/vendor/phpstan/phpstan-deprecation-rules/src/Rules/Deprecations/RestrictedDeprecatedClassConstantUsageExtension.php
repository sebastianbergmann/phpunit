<?php declare(strict_types = 1);

namespace PHPStan\Rules\Deprecations;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassConstantReflection;
use PHPStan\Rules\RestrictedUsage\RestrictedClassConstantUsageExtension;
use PHPStan\Rules\RestrictedUsage\RestrictedUsage;
use function sprintf;
use function strtolower;

class RestrictedDeprecatedClassConstantUsageExtension implements RestrictedClassConstantUsageExtension
{

	private DeprecatedScopeHelper $deprecatedScopeHelper;

	public function __construct(DeprecatedScopeHelper $deprecatedScopeHelper)
	{
		$this->deprecatedScopeHelper = $deprecatedScopeHelper;
	}

	public function isRestrictedClassConstantUsage(
		ClassConstantReflection $constantReflection,
		Scope $scope
	): ?RestrictedUsage
	{
		// Foo::class is reported as a deprecated class name usage
		if ($constantReflection->getName() === 'class') {
			return null;
		}

		if ($this->deprecatedScopeHelper->isScopeDeprecated($scope)) {
			return null;
		}

		if ($constantReflection->getDeclaringClass()->isDeprecated()) {
			$class = $constantReflection->getDeclaringClass();
			$classDescription = $class->getDeprecatedDescription();
			if ($classDescription === null) {
				return RestrictedUsage::create(
					sprintf(
						'Fetching class constant %s of deprecated %s %s.',
						$constantReflection->getName(),
						strtolower($constantReflection->getDeclaringClass()->getClassTypeDescription()),
						$constantReflection->getDeclaringClass()->getDisplayName(),
					),
					sprintf(
						'classConstant.deprecated%s',
						$constantReflection->getDeclaringClass()->getClassTypeDescription(),
					),
				);
			}

			return RestrictedUsage::create(
				sprintf(
					"Fetching class constant %s of deprecated %s %s:\n%s",
					$constantReflection->getName(),
					strtolower($constantReflection->getDeclaringClass()->getClassTypeDescription()),
					$constantReflection->getDeclaringClass()->getDisplayName(),
					$classDescription,
				),
				sprintf(
					'classConstant.deprecated%s',
					$constantReflection->getDeclaringClass()->getClassTypeDescription(),
				),
			);
		}

		if (!$constantReflection->isDeprecated()->yes()) {
			return null;
		}

		$description = $constantReflection->getDeprecatedDescription();
		if ($description === null) {
			return RestrictedUsage::create(
				sprintf(
					'Fetching deprecated class constant %s of %s %s.',
					$constantReflection->getName(),
					strtolower($constantReflection->getDeclaringClass()->getClassTypeDescription()),
					$constantReflection->getDeclaringClass()->getDisplayName(),
				),
				'classConstant.deprecated',
			);
		}

		return RestrictedUsage::create(
			sprintf(
				"Fetching deprecated class constant %s of %s %s:\n%s",
				$constantReflection->getName(),
				strtolower($constantReflection->getDeclaringClass()->getClassTypeDescription()),
				$constantReflection->getDeclaringClass()->getDisplayName(),
				$description,
			),
			'classConstant.deprecated',
		);
	}

}
