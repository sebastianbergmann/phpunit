<?php declare(strict_types = 1);

namespace PHPStan\Rules\ForLoop;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Stmt\For_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function is_string;
use function sprintf;

/**
 * @implements Rule<For_>
 */
class OverwriteVariablesWithForLoopInitRule implements Rule
{

	public function getNodeType(): string
	{
		return For_::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$errors = [];
		foreach ($node->init as $expr) {
			if (!($expr instanceof Assign)) {
				continue;
			}

			foreach ($this->checkValueVar($scope, $expr->var) as $error) {
				$errors[] = $error;
			}
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
			$errors[] = RuleErrorBuilder::message(sprintf('For loop initial assignment overwrites variable $%s.', $expr->name))
				->identifier('for.variableOverwrite')
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
