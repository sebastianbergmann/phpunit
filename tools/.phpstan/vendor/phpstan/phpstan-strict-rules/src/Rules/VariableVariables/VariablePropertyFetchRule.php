<?php declare(strict_types = 1);

namespace PHPStan\Rules\VariableVariables;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
use SimpleXMLElement;
use function sprintf;

/**
 * @implements Rule<PropertyFetch>
 */
class VariablePropertyFetchRule implements Rule
{

	private ReflectionProvider $reflectionProvider;

	/** @var string[] */
	private array $universalObjectCratesClasses;

	/**
	 * @param string[] $universalObjectCratesClasses
	 */
	public function __construct(ReflectionProvider $reflectionProvider, array $universalObjectCratesClasses)
	{
		$this->reflectionProvider = $reflectionProvider;
		$this->universalObjectCratesClasses = $universalObjectCratesClasses;
	}

	public function getNodeType(): string
	{
		return PropertyFetch::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->name instanceof Node\Identifier) {
			return [];
		}

		$fetchedOnType = $scope->getType($node->var);
		foreach ($fetchedOnType->getObjectClassNames() as $referencedClass) {
			if (!$this->reflectionProvider->hasClass($referencedClass)) {
				continue;
			}

			$classReflection = $this->reflectionProvider->getClass($referencedClass);
			if (
				$this->isUniversalObjectCrate($classReflection)
				|| $this->isSimpleXMLElement($classReflection)
			) {
				return [];
			}
		}

		return [
			RuleErrorBuilder::message(sprintf(
				'Variable property access on %s.',
				$fetchedOnType->describe(VerbosityLevel::typeOnly()),
			))->identifier('property.dynamicName')->build(),
		];
	}

	private function isSimpleXMLElement(
		ClassReflection $classReflection
	): bool
	{
		return $classReflection->is(SimpleXMLElement::class);
	}

	private function isUniversalObjectCrate(
		ClassReflection $classReflection
	): bool
	{
		foreach ($this->universalObjectCratesClasses as $className) {
			if (!$this->reflectionProvider->hasClass($className)) {
				continue;
			}

			if ($classReflection->is($className)) {
				return true;
			}
		}

		return false;
	}

}
