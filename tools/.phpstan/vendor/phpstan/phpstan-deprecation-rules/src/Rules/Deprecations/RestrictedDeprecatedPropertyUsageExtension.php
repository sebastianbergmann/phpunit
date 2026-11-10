<?php declare(strict_types = 1);

namespace PHPStan\Rules\Deprecations;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ExtendedPropertyReflection;
use PHPStan\Rules\RestrictedUsage\RestrictedPropertyUsageExtension;
use PHPStan\Rules\RestrictedUsage\RestrictedUsage;
use function sprintf;
use function strtolower;

class RestrictedDeprecatedPropertyUsageExtension implements RestrictedPropertyUsageExtension
{

	private DeprecatedScopeHelper $deprecatedScopeHelper;

	public function __construct(DeprecatedScopeHelper $deprecatedScopeHelper)
	{
		$this->deprecatedScopeHelper = $deprecatedScopeHelper;
	}

	public function isRestrictedPropertyUsage(
		ExtendedPropertyReflection $propertyReflection,
		Scope $scope
	): ?RestrictedUsage
	{
		if ($this->deprecatedScopeHelper->isScopeDeprecated($scope)) {
			return null;
		}

		if ($propertyReflection->getDeclaringClass()->isDeprecated()) {
			$class = $propertyReflection->getDeclaringClass();
			$classDescription = $class->getDeprecatedDescription();
			if ($classDescription === null) {
				return RestrictedUsage::create(
					sprintf(
						'Access to %sproperty $%s of deprecated %s %s.',
						$propertyReflection->isStatic() ? 'static ' : '',
						$propertyReflection->getName(),
						strtolower($propertyReflection->getDeclaringClass()->getClassTypeDescription()),
						$propertyReflection->getDeclaringClass()->getName(),
					),
					sprintf(
						'%s.deprecated%s',
						$propertyReflection->isStatic() ? 'staticProperty' : 'property',
						$propertyReflection->getDeclaringClass()->getClassTypeDescription(),
					),
				);
			}

			return RestrictedUsage::create(
				sprintf(
					"Access to %sproperty $%s of deprecated %s %s:\n%s",
					$propertyReflection->isStatic() ? 'static ' : '',
					$propertyReflection->getName(),
					strtolower($propertyReflection->getDeclaringClass()->getClassTypeDescription()),
					$propertyReflection->getDeclaringClass()->getName(),
					$classDescription,
				),
				sprintf(
					'%s.deprecated%s',
					$propertyReflection->isStatic() ? 'staticProperty' : 'property',
					$propertyReflection->getDeclaringClass()->getClassTypeDescription(),
				),
			);
		}

		if (!$propertyReflection->isDeprecated()->yes()) {
			return null;
		}

		$description = $propertyReflection->getDeprecatedDescription();
		if ($description === null) {
			return RestrictedUsage::create(
				sprintf(
					'Access to deprecated %sproperty $%s of %s %s.',
					$propertyReflection->isStatic() ? 'static ' : '',
					$propertyReflection->getName(),
					strtolower($propertyReflection->getDeclaringClass()->getClassTypeDescription()),
					$propertyReflection->getDeclaringClass()->getName(),
				),
				sprintf('%s.deprecated', $propertyReflection->isStatic() ? 'staticProperty' : 'property'),
			);
		}

		return RestrictedUsage::create(
			sprintf(
				"Access to deprecated %sproperty $%s of %s %s:\n%s",
				$propertyReflection->isStatic() ? 'static ' : '',
				$propertyReflection->getName(),
				strtolower($propertyReflection->getDeclaringClass()->getClassTypeDescription()),
				$propertyReflection->getDeclaringClass()->getName(),
				$description,
			),
			sprintf('%s.deprecated', $propertyReflection->isStatic() ? 'staticProperty' : 'property'),
		);
	}

}
