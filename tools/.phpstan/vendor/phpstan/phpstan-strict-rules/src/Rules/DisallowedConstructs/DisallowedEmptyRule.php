<?php declare(strict_types = 1);

namespace PHPStan\Rules\DisallowedConstructs;

use PhpParser\Node;
use PhpParser\Node\Expr\Empty_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Empty_>
 */
class DisallowedEmptyRule implements Rule
{

	public function getNodeType(): string
	{
		return Empty_::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		return [
			RuleErrorBuilder::message('Construct empty() is not allowed. Use more strict comparison.')
				->identifier('empty.notAllowed')
				->build(),
		];
	}

}
