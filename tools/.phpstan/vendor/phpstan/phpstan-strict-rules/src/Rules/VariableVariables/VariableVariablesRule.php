<?php declare(strict_types = 1);

namespace PHPStan\Rules\VariableVariables;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function is_string;

/**
 * @implements Rule<Variable>
 */
class VariableVariablesRule implements Rule
{

	public function getNodeType(): string
	{
		return Variable::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if (is_string($node->name)) {
			return [];
		}

		return [
			RuleErrorBuilder::message('Variable variables are not allowed.')
				->identifier('variable.dynamicName')
				->build(),
		];
	}

}
