<?php declare(strict_types = 1);

namespace PHPStan\Rules\Operators;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\AssignOp\Mul as AssignOpMul;
use PhpParser\Node\Expr\BinaryOp\Mul as BinaryOpMul;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\MultipleNodeTypesRule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
use function sprintf;

/**
 * @implements MultipleNodeTypesRule<Expr>
 */
class OperandsInArithmeticMultiplicationRule implements MultipleNodeTypesRule
{

	private OperatorRuleHelper $helper;

	public function __construct(OperatorRuleHelper $helper)
	{
		$this->helper = $helper;
	}

	public function getNodeTypes(): array
	{
		return [BinaryOpMul::class, AssignOpMul::class];
	}

	public function getNodeType(): string
	{
		return Expr::class;
	}

	public function processNode(Node $node, Scope $scope): array
	{
		if ($node instanceof BinaryOpMul) {
			$left = $node->left;
			$right = $node->right;
		} elseif ($node instanceof AssignOpMul) {
			$left = $node->var;
			$right = $node->expr;
		} else {
			return [];
		}

		$messages = [];
		$leftType = $scope->getType($left);
		if (!$this->helper->isValidForArithmeticOperation($scope, $left)) {
			$messages[] = RuleErrorBuilder::message(sprintf(
				'Only numeric types are allowed in *, %s given on the left side.',
				$leftType->describe(VerbosityLevel::typeOnly()),
			))->identifier('mul.leftNonNumeric')->build();
		}

		$rightType = $scope->getType($right);
		if (!$this->helper->isValidForArithmeticOperation($scope, $right)) {
			$messages[] = RuleErrorBuilder::message(sprintf(
				'Only numeric types are allowed in *, %s given on the right side.',
				$rightType->describe(VerbosityLevel::typeOnly()),
			))->identifier('mul.rightNonNumeric')->build();
		}

		return $messages;
	}

}
