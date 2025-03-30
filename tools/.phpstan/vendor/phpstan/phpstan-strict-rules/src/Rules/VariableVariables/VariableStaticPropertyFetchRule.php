<?php declare(strict_types = 1);

namespace PHPStan\Rules\VariableVariables;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
use function sprintf;

/**
 * @implements Rule<StaticPropertyFetch>
 */
class VariableStaticPropertyFetchRule implements Rule
{

	public function getNodeType(): string
	{
		return StaticPropertyFetch::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->name instanceof Node\Identifier) {
			return [];
		}

		if ($node->class instanceof Node\Name) {
			$propertyAccessedOn = $scope->resolveName($node->class);
		} else {
			$propertyAccessedOn = $scope->getType($node->class)->describe(VerbosityLevel::typeOnly());
		}

		return [
			RuleErrorBuilder::message(sprintf(
				'Variable static property access on %s.',
				$propertyAccessedOn,
			))->identifier('staticProperty.dynamicName')->build(),
		];
	}

}
