<?php declare(strict_types = 1);

namespace PHPStan\Rules\VariableVariables;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
use function sprintf;

/**
 * @implements Rule<StaticCall>
 */
class VariableStaticMethodCallRule implements Rule
{

	public function getNodeType(): string
	{
		return StaticCall::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if ($node->name instanceof Node\Identifier) {
			return [];
		}

		if ($node->class instanceof Node\Name) {
			$methodCalledOn = $scope->resolveName($node->class);
		} else {
			$methodCalledOn = $scope->getType($node->class)->describe(VerbosityLevel::typeOnly());
		}

		return [
			RuleErrorBuilder::message(sprintf(
				'Variable static method call on %s.',
				$methodCalledOn,
			))->identifier('staticMethod.dynamicName')->build(),
		];
	}

}
