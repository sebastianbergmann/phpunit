<?php declare(strict_types = 1);

namespace PHPStan\Rules\Operators;

use PhpParser\Node\Expr\PostInc;

/**
 * @phpstan-extends OperandInArithmeticIncrementOrDecrementRule<PostInc>
 */
class OperandInArithmeticPostIncrementRule extends OperandInArithmeticIncrementOrDecrementRule
{

	public function getNodeType(): string
	{
		return PostInc::class;
	}

	protected function describeOperation(): string
	{
		return 'post-increment';
	}

	protected function getIdentifier(): string
	{
		return 'postInc';
	}

}
