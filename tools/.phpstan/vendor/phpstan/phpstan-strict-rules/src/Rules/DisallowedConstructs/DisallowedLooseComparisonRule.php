<?php declare(strict_types = 1);

namespace PHPStan\Rules\DisallowedConstructs;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<BinaryOp>
 */
class DisallowedLooseComparisonRule implements Rule
{

	public function getNodeType(): string
	{
		return BinaryOp::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if ($node instanceof Equal) {
			return [
				RuleErrorBuilder::message(
					'Loose comparison via "==" is not allowed.',
				)->tip('Use strict comparison via "===" instead.')
					->identifier('equal.notAllowed')
					->build(),
			];
		}
		if ($node instanceof NotEqual) {
			return [
				RuleErrorBuilder::message(
					'Loose comparison via "!=" is not allowed.',
				)->tip('Use strict comparison via "!==" instead.')
					->identifier('notEqual.notAllowed')
					->build(),
			];
		}

		return [];
	}

}
