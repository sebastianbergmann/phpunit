<?php declare(strict_types = 1);

namespace PHPStan\Rules\DisallowedConstructs;

use PhpParser\Node;
use PhpParser\Node\Expr\Ternary;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

/**
 * @implements Rule<Ternary>
 */
class DisallowedShortTernaryRule implements Rule
{

	public function getNodeType(): string
	{
		return Ternary::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->if !== null) {
			return [];
		}

		return [
			RuleErrorBuilder::message('Short ternary operator is not allowed. Use null coalesce operator if applicable or consider using long ternary.')
				->identifier('ternary.shortNotAllowed')
				->build(),
		];
	}

}
