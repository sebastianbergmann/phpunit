<?php declare(strict_types = 1);

namespace PHPStan\Rules\Operators;

use PhpParser\Node;
use PhpParser\Node\Expr\PostDec;
use PhpParser\Node\Expr\PostInc;
use PhpParser\Node\Expr\PreDec;
use PhpParser\Node\Expr\PreInc;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\VerbosityLevel;
use function sprintf;

/**
 * @phpstan-template TNodeType of PreInc|PreDec|PostInc|PostDec
 * @phpstan-implements Rule<TNodeType>
 */
abstract class OperandInArithmeticIncrementOrDecrementRule implements Rule
{

	private OperatorRuleHelper $helper;

	public function __construct(OperatorRuleHelper $helper)
	{
		$this->helper = $helper;
	}

	/**
	 * @param TNodeType $node
	 */
	public function processNode(Node $node, Scope $scope): array
	{
		$messages = [];
		$varType = $scope->getType($node->var);

		if (
			($node instanceof PreInc || $node instanceof PostInc)
				&& !$this->helper->isValidForIncrement($scope, $node->var)
			|| ($node instanceof PreDec || $node instanceof PostDec)
				&& !$this->helper->isValidForDecrement($scope, $node->var)
		) {
			$messages[] = RuleErrorBuilder::message(sprintf(
				'Only numeric types are allowed in %s, %s given.',
				$this->describeOperation(),
				$varType->describe(VerbosityLevel::typeOnly()),
			))->identifier(sprintf('%s.nonNumeric', $this->getIdentifier()))->build();
		}

		return $messages;
	}

	abstract protected function describeOperation(): string;

	/**
	 * @return 'preInc'|'postInc'|'preDec'|'postDec'
	 */
	abstract protected function getIdentifier(): string;

}
