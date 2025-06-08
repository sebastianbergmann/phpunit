<?php declare(strict_types = 1);

namespace PHPStan\Rules\VariableVariables;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\MethodCallableNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
use function sprintf;

/**
 * @implements Rule<MethodCallableNode>
 */
class VariableMethodCallableRule implements Rule
{

	public function getNodeType(): string
	{
		return MethodCallableNode::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->getName() instanceof Node\Identifier) {
			return [];
		}

		return [
			RuleErrorBuilder::message(sprintf(
				'Variable method call on %s.',
				$scope->getType($node->getVar())->describe(VerbosityLevel::typeOnly()),
			))->identifier('method.dynamicName')->build(),
		];
	}

}
