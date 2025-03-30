<?php declare(strict_types = 1);

namespace PHPStan\Rules\BooleansInConditions;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
use function sprintf;

/**
 * @implements Rule<BooleanNot>
 */
class BooleanInBooleanNotRule implements Rule
{

	private BooleanRuleHelper $helper;

	public function __construct(BooleanRuleHelper $helper)
	{
		$this->helper = $helper;
	}

	public function getNodeType(): string
	{
		return BooleanNot::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if ($this->helper->passesAsBoolean($scope, $node->expr)) {
			return [];
		}

		$expressionType = $scope->getType($node->expr);

		return [
			RuleErrorBuilder::message(sprintf(
				'Only booleans are allowed in a negated boolean, %s given.',
				$expressionType->describe(VerbosityLevel::typeOnly()),
			))->identifier('booleanNot.exprNotBoolean')->build(),
		];
	}

}
