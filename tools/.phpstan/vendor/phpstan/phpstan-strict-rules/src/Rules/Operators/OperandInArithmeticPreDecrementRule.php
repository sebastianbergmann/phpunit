<?php declare(strict_types = 1);

namespace PHPStan\Rules\Operators;

use PhpParser\Node\Expr\PreDec;

/**
 * @phpstan-extends OperandInArithmeticIncrementOrDecrementRule<PreDec>
 */
class OperandInArithmeticPreDecrementRule extends OperandInArithmeticIncrementOrDecrementRule
{

	public function getNodeType(): string
	{
		return PreDec::class;
	}

	protected function describeOperation(): string
	{
		return 'pre-decrement';
	}

	protected function getIdentifier(): string
	{
		return 'preDec';
	}

}
