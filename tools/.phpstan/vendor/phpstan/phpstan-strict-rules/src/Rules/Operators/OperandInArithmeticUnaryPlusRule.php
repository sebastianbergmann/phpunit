<?php declare(strict_types = 1);

namespace PHPStan\Rules\Operators;

use PhpParser\Node;
use PhpParser\Node\Expr\UnaryPlus;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
use function sprintf;

/**
 * @phpstan-implements Rule<UnaryPlus>
 */
class OperandInArithmeticUnaryPlusRule implements Rule
{

	private OperatorRuleHelper $helper;

	public function __construct(OperatorRuleHelper $helper)
	{
		$this->helper = $helper;
	}

	public function getNodeType(): string
	{
		return UnaryPlus::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		$messages = [];

		if (!$this->helper->isValidForArithmeticOperation($scope, $node->expr)) {
			$varType = $scope->getType($node->expr);

			$messages[] = RuleErrorBuilder::message(sprintf(
				'Only numeric types are allowed in unary +, %s given.',
				$varType->describe(VerbosityLevel::typeOnly()),
			))->identifier('unaryPlus.nonNumeric')->build();
		}

		return $messages;
	}

}
