<?php declare(strict_types = 1);

namespace PHPStan\Rules\DisallowedConstructs;

use PhpParser\Node;
use PhpParser\Node\Expr\ShellExec;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<ShellExec>
 */
class DisallowedBacktickRule implements Rule
{

	public function getNodeType(): string
	{
		return ShellExec::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		return [
			RuleErrorBuilder::message('Backtick operator is not allowed. Use shell_exec() instead.')
				->identifier('backtick.notAllowed')
				->build(),
		];
	}

}
