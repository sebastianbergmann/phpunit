<?php declare(strict_types = 1);

namespace PHPStan\Rules\Operators;

use PhpParser\Node\Expr\PostDec;

/**
 * @phpstan-extends OperandInArithmeticIncrementOrDecrementRule<PostDec>
 */
class OperandInArithmeticPostDecrementRule extends OperandInArithmeticIncrementOrDecrementRule
{

	public function getNodeType(): string
	{
		return PostDec::class;
	}

	protected function describeOperation(): string
	{
		return 'post-decrement';
	}

	protected function getIdentifier(): string
	{
		return 'postDec';
	}

}
