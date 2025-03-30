<?php declare(strict_types = 1);

namespace PHPStan\Rules\ForeachLoop;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function is_string;
use function sprintf;

/**
 * @implements Rule<Foreach_>
 */
class OverwriteVariablesWithForeachRule implements Rule
{

	public function getNodeType(): string
	{
		return Foreach_::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$errors = [];
		if (
			$node->keyVar instanceof Node\Expr\Variable
			&& is_string($node->keyVar->name)
			&& $scope->hasVariableType($node->keyVar->name)->yes()
		) {
			$errors[] = RuleErrorBuilder::message(sprintf('Foreach overwrites $%s with its key variable.', $node->keyVar->name))
				->identifier('foreach.keyOverwrite')
				->build();
		}

		foreach ($this->checkValueVar($scope, $node->valueVar) as $error) {
			$errors[] = $error;
		}

		return $errors;
	}

	/**
	 * @return list<IdentifierRuleError>
	 */
	private function checkValueVar(Scope $scope, Expr $expr): array
	{
		$errors = [];
		if (
			$expr instanceof Node\Expr\Variable
			&& is_string($expr->name)
			&& $scope->hasVariableType($expr->name)->yes()
		) {
			$errors[] = RuleErrorBuilder::message(sprintf('Foreach overwrites $%s with its value variable.', $expr->name))
				->identifier('foreach.valueOverwrite')
				->build();
		}

		if (
			$expr instanceof Node\Expr\List_
			|| $expr instanceof Node\Expr\Array_
		) {
			foreach ($expr->items as $item) {
				if ($item === null) {
					continue;
				}

				foreach ($this->checkValueVar($scope, $item->value) as $error) {
					$errors[] = $error;
				}
			}
		}

		return $errors;
	}

}
