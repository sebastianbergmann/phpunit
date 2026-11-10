<?php declare(strict_types = 1);

namespace PHPStan\Rules\Deprecations;

use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\ClassNameUsageLocation;
use PHPStan\Rules\RestrictedUsage\RestrictedClassNameUsageExtension;
use PHPStan\Rules\RestrictedUsage\RestrictedUsage;
use function rtrim;
use function sprintf;
use function strtolower;

class RestrictedDeprecatedClassNameUsageExtension implements RestrictedClassNameUsageExtension
{

	private DeprecatedScopeHelper $deprecatedScopeHelper;

	private ReflectionProvider $reflectionProvider;

	private bool $bleedingEdge;

	public function __construct(
		DeprecatedScopeHelper $deprecatedScopeHelper,
		ReflectionProvider $reflectionProvider,
		bool $bleedingEdge
	)
	{
		$this->deprecatedScopeHelper = $deprecatedScopeHelper;
		$this->reflectionProvider = $reflectionProvider;
		$this->bleedingEdge = $bleedingEdge;
	}

	public function isRestrictedClassNameUsage(
		ClassReflection $classReflection,
		Scope $scope,
		ClassNameUsageLocation $location
	): ?RestrictedUsage
	{
		if (!$classReflection->isDeprecated()) {
			return null;
		}

		if ($this->deprecatedScopeHelper->isScopeDeprecated($scope)) {
			return null;
		}

		$currentClassName = $location->getCurrentClassName();
		if ($currentClassName !== null && $this->reflectionProvider->hasClass($currentClassName)) {
			$currentClassReflection = $this->reflectionProvider->getClass($currentClassName);
			if ($currentClassReflection->isDeprecated()) {
				return null;
			}
		}

		$identifierPart = sprintf('deprecated%s', $classReflection->getClassTypeDescription());
		$defaultUsage = RestrictedUsage::create(
			$this->addClassDescriptionToMessage($classReflection, $location->createMessage(
				sprintf('deprecated %s %s', strtolower($classReflection->getClassTypeDescription()), $classReflection->getDisplayName()),
			)),
			$location->createIdentifier($identifierPart),
		);

		if ($location->value === ClassNameUsageLocation::CLASS_IMPLEMENTS) {
			return $defaultUsage;
		}

		if ($location->value === ClassNameUsageLocation::CLASS_EXTENDS) {
			return $defaultUsage;
		}

		if ($location->value === ClassNameUsageLocation::INTERFACE_EXTENDS) {
			return $defaultUsage;
		}

		if ($location->value === ClassNameUsageLocation::INSTANTIATION) {
			return $defaultUsage;
		}

		if ($location->value === ClassNameUsageLocation::TRAIT_USE) {
			return $defaultUsage;
		}

		if ($location->value === ClassNameUsageLocation::STATIC_METHOD_CALL) {
			$method = $location->getMethod();
			if ($method !== null) {
				if ($method->isDeprecated()->yes() || $method->getDeclaringClass()->isDeprecated()) {
					return null;
				}
			}

			return $defaultUsage;
		}

		if ($location->value === ClassNameUsageLocation::STATIC_PROPERTY_ACCESS) {
			$property = $location->getProperty();
			if ($property !== null) {
				if ($property->isDeprecated()->yes() || $property->getDeclaringClass()->isDeprecated()) {
					return null;
				}
			}

			return $defaultUsage;
		}

		if ($location->value === ClassNameUsageLocation::CLASS_CONSTANT_ACCESS) {
			$constant = $location->getClassConstant();
			if ($constant !== null) {
				if ($constant->isDeprecated()->yes() || $constant->getDeclaringClass()->isDeprecated()) {
					return null;
				}
			}

			return $defaultUsage;
		}

		if ($location->value === ClassNameUsageLocation::PARAMETER_TYPE || $location->value === ClassNameUsageLocation::RETURN_TYPE) {
			return $defaultUsage;
		}

		if (!$this->bleedingEdge) {
			return null;
		}

		return $defaultUsage;
	}

	private function addClassDescriptionToMessage(ClassReflection $classReflection, string $message): string
	{
		if ($classReflection->getDeprecatedDescription() === null) {
			return $message;
		}

		return rtrim($message, '.') . ":\n" . $classReflection->getDeprecatedDescription();
	}

}
