<?php declare(strict_types = 1);

namespace PHPStan\Rules\DisallowedConstructs;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
use function sprintf;

/**
 * @implements Rule<BinaryOp>
 */
class DisallowedLooseComparisonRule implements Rule
{

	private bool $includeOperandTypesInErrorMessage;

	public function __construct(bool $includeOperandTypesInErrorMessage)
	{
		$this->includeOperandTypesInErrorMessage = $includeOperandTypesInErrorMessage;
	}

	public function getNodeType(): string
	{
		return BinaryOp::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if (!$node instanceof Equal && !$node instanceof NotEqual) {
			return [];
		}

		$left = $scope->getType($node->left)->describe(VerbosityLevel::typeOnly());
		$right = $scope->getType($node->right)->describe(VerbosityLevel::typeOnly());

		if ($node instanceof Equal) {
			return [
				RuleErrorBuilder::message(
					$this->includeOperandTypesInErrorMessage
						? sprintf('Loose comparison via "==" between %s and %s is not allowed.', $left, $right)
						: 'Loose comparison via "==" is not allowed.',
				)->tip('Use strict comparison via "===" instead.')
					->identifier('equal.notAllowed')
					->build(),
			];
		}

		return [
			RuleErrorBuilder::message(
				$this->includeOperandTypesInErrorMessage
					? sprintf('Loose comparison via "!=" between %s and %s is not allowed.', $left, $right)
					: 'Loose comparison via "!=" is not allowed.',
			)->tip('Use strict comparison via "!==" instead.')
				->identifier('notEqual.notAllowed')
				->build(),
		];
	}

}
