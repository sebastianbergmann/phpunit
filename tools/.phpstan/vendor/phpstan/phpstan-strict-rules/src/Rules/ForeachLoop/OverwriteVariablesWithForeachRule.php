<?php declare(strict_types = 1);

namespace PHPStan\Rules\ForeachLoop;

use PhpParser\Node;
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\Analyser\Scope;
use PHPStan\Node\Variable\VariableWrite;
use PHPStan\Node\VariableWritesNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function sprintf;

/**
 * @implements Rule<VariableWritesNode>
 */
class OverwriteVariablesWithForeachRule implements Rule
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
			// only a foreach that takes over a variable assigned before the loop
			// and read after it - reusing a spent loop variable is harmless
			$loop = $node->getVariableOverwritingLoop($write);
			if (!$loop instanceof Foreach_) {
				continue;
			}

			$isKey = $write->getKind() === VariableWrite::KIND_FOREACH_KEY;
			$errors[] = RuleErrorBuilder::message(sprintf(
				'Foreach overwrites $%s with its %s variable.',
				$write->getVariableName(),
				$isKey ? 'key' : 'value',
			))
				->identifier($isKey ? 'foreach.keyOverwrite' : 'foreach.valueOverwrite')
				->line($loop->getStartLine())
				->build();
		}

		return $errors;
	}

}
