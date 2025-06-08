<?php declare(strict_types = 1);

namespace PHPStan\Rules\Functions;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ThisType;
use function is_string;
use function sprintf;

/**
 * @implements Rule<Node\Expr\Closure>
 */
class ClosureUsesThisRule implements Rule
{

	public function getNodeType(): string
	{
		return Node\Expr\Closure::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->static) {
			return [];
		}

		if ($scope->isInClosureBind()) {
			return [];
		}

		$messages = [];
		foreach ($node->uses as $closureUse) {
			$varType = $scope->getType($closureUse->var);
			if (!is_string($closureUse->var->name)) {
				continue;
			}
			if (!$varType instanceof ThisType) {
				continue;
			}

			$messages[] = RuleErrorBuilder::message(sprintf('Anonymous function uses $this assigned to variable $%s. Use $this directly in the function body.', $closureUse->var->name))
				->line($closureUse->getStartLine())
				->identifier('closure.useThis')
				->build();
		}
		return $messages;
	}

}
