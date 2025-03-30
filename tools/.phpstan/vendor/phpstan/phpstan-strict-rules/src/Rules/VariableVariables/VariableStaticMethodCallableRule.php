<?php declare(strict_types = 1);

namespace PHPStan\Rules\VariableVariables;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\StaticMethodCallableNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
use function sprintf;

/**
 * @implements Rule<StaticMethodCallableNode>
 */
class VariableStaticMethodCallableRule implements Rule
{

	public function getNodeType(): string
	{
		return StaticMethodCallableNode::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->getName() instanceof Node\Identifier) {
			return [];
		}

		if ($node->getClass() instanceof Node\Name) {
			$methodCalledOn = $scope->resolveName($node->getClass());
		} else {
			$methodCalledOn = $scope->getType($node->getClass())->describe(VerbosityLevel::typeOnly());
		}

		return [
			RuleErrorBuilder::message(sprintf(
				'Variable static method call on %s.',
				$methodCalledOn,
			))->identifier('staticMethod.dynamicName')->build(),
		];
	}

}
