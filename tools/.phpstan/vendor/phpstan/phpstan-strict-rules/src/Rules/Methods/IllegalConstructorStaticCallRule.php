<?php declare(strict_types = 1);

namespace PHPStan\Rules\Methods;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function array_key_exists;
use function array_map;
use function in_array;
use function sprintf;
use function strtolower;

/**
 * @implements Rule<Node\Expr\StaticCall>
 */
final class IllegalConstructorStaticCallRule implements Rule
{

	public function getNodeType(): string
	{
		return Node\Expr\StaticCall::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->name instanceof Node\Identifier || $node->name->toLowerString() !== '__construct') {
			return [];
		}

		if ($this->isCollectCallingConstructor($node, $scope)) {
			return [];
		}

		return [
			RuleErrorBuilder::message('Static call to __construct() is only allowed on a parent class in the constructor.')
				->identifier('constructor.call')
				->build(),
		];
	}

	private function isCollectCallingConstructor(Node\Expr\StaticCall $node, Scope $scope): bool
	{
		// __construct should be called from inside constructor
		if ($scope->getFunction() === null) {
			return false;
		}

		if ($scope->getFunction()->getName() !== '__construct') {
			if (!$this->isInRenamedTraitConstructor($scope)) {
				return false;
			}
		}

		if (!$scope->isInClass()) {
			return false;
		}

		if (!$node->class instanceof Node\Name) {
			return false;
		}

		$parentClasses = array_map(static fn (string $name) => strtolower($name), $scope->getClassReflection()->getParentClassesNames());

		return in_array(strtolower($scope->resolveName($node->class)), $parentClasses, true);
	}

	private function isInRenamedTraitConstructor(Scope $scope): bool
	{
		if (!$scope->isInClass()) {
			return false;
		}

		if (!$scope->isInTrait()) {
			return false;
		}

		if ($scope->getFunction() === null) {
			return false;
		}

		$traitAliases = $scope->getClassReflection()->getNativeReflection()->getTraitAliases();
		$functionName = $scope->getFunction()->getName();
		if (!array_key_exists($functionName, $traitAliases)) {
			return false;
		}

		return $traitAliases[$functionName] === sprintf('%s::%s', $scope->getTraitReflection()->getName(), '__construct');
	}

}
