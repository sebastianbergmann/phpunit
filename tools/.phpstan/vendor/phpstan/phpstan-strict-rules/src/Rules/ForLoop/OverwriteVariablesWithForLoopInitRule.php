<?php declare(strict_types = 1);

namespace PHPStan\Rules\ForLoop;

use PhpParser\Node;
use PhpParser\Node\Stmt\For_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\VariableWritesNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function sprintf;

/**
 * @implements Rule<VariableWritesNode>
 */
class OverwriteVariablesWithForLoopInitRule implements Rule
{

	public function getNodeType(): string
	{
		return VariableWritesNode::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->isOpaque()) {
			return [];
		}

		$errors = [];
		foreach ($node->getWrites() as $write) {
			// only a for loop that takes over a variable assigned before the loop
			// and read after it - reusing a spent loop variable is harmless
			$loop = $node->getVariableOverwritingLoop($write);
			if (!$loop instanceof For_) {
				continue;
			}

			$errors[] = RuleErrorBuilder::message(sprintf('For loop initial assignment overwrites variable $%s.', $write->getVariableName()))
				->identifier('for.variableOverwrite')
				->line($loop->getStartLine())
				->build();
		}

		return $errors;
	}

}
