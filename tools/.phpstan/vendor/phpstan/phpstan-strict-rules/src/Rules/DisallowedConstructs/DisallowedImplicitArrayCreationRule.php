<?php declare(strict_types = 1);

namespace PHPStan\Rules\DisallowedConstructs;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function is_string;
use function sprintf;

/**
 * @implements Rule<Assign>
 */
class DisallowedImplicitArrayCreationRule implements Rule
{

	public function getNodeType(): string
	{
		return Assign::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node->var instanceof ArrayDimFetch) {
			return [];
		}

		$node = $node->var;
		while ($node instanceof ArrayDimFetch) {
			$node = $node->var;
		}

		if (!$node instanceof Variable) {
			return [];
		}

		if (!is_string($node->name)) {
			return [];
		}

		$certainty = $scope->hasVariableType($node->name);
		if ($certainty->no()) {
			return [
				RuleErrorBuilder::message(sprintf('Implicit array creation is not allowed - variable $%s does not exist.', $node->name))
					->identifier('variable.implicitArray')
					->build(),
			];
		}

		if ($certainty->maybe()) {
			return [
				RuleErrorBuilder::message(sprintf('Implicit array creation is not allowed - variable $%s might not exist.', $node->name))
					->identifier('variable.implicitArray')
					->build(),
			];
		}

		return [];
	}

}
