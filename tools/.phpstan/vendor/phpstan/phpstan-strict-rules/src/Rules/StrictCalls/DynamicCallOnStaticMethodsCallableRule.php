<?php declare(strict_types = 1);

namespace PHPStan\Rules\StrictCalls;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\MethodCallableNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Rules\RuleLevelHelper;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Type;
use function sprintf;

/**
 * @implements Rule<MethodCallableNode>
 */
class DynamicCallOnStaticMethodsCallableRule implements Rule
{

	private RuleLevelHelper $ruleLevelHelper;

	public function __construct(RuleLevelHelper $ruleLevelHelper)
	{
		$this->ruleLevelHelper = $ruleLevelHelper;
	}

	public function getNodeType(): string
	{
		return MethodCallableNode::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->getName() instanceof Node\Identifier) {
			return [];
		}

		$name = $node->getName()->name;
		$type = $this->ruleLevelHelper->findTypeToCheck(
			$scope,
			$node->getVar(),
			'',
			static fn (Type $type): bool => $type->canCallMethods()->yes() && $type->hasMethod($name)->yes(),
		)->getType();

		if ($type instanceof ErrorType || !$type->canCallMethods()->yes() || !$type->hasMethod($name)->yes()) {
			return [];
		}

		$methodReflection = $type->getMethod($name, $scope);
		if ($methodReflection->isStatic()) {
			return [
				RuleErrorBuilder::message(sprintf(
					'Dynamic call to static method %s::%s().',
					$methodReflection->getDeclaringClass()->getDisplayName(),
					$methodReflection->getName(),
				))->identifier('staticMethod.dynamicCall')->build(),
			];
		}

		return [];
	}

}
