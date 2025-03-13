<?php declare(strict_types = 1);

namespace PHPStan\Rules\VariableVariables;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
use function sprintf;

/**
 * @implements Rule<MethodCall>
 */
class VariableMethodCallRule implements Rule
{

	public function getNodeType(): string
	{
		return MethodCall::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->name instanceof Node\Identifier) {
			return [];
		}

		return [
			RuleErrorBuilder::message(sprintf(
				'Variable method call on %s.',
				$scope->getType($node->var)->describe(VerbosityLevel::typeOnly()),
			))->identifier('method.dynamicName')->build(),
		];
	}

}
