<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use function array_map;
use function array_values;
use function count;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract class BinaryOperator extends Operator
{
    /**
     * @var Constraint[]
     */
    private $constraints = [];

    public static function fromConstraints(Constraint ...$constraints): self
    {
        $constraint = new static;

        $constraint->constraints = $constraints;

        return $constraint;
    }

    /**
     * @param mixed[] $constraints
     */
    public function setConstraints(array $constraints): void
    {
        $this->constraints = array_map(function ($constraint): Constraint {
            return $this->checkConstraint($constraint);
        }, array_values($constraints));
    }

    /**
     * Returns the number of operands (constraints).
     */
    final public function arity(): int
    {
        return count($this->constraints);
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        $reduced = $this->reduce();

        if ($reduced !== $this) {
            return $reduced->toString();
        }

        $text = '';

        foreach ($this->constraints as $key => $constraint) {
            $constraint = $constraint->reduce();

            $text .= $this->constraintToString($constraint, $key);
        }

        return $text;
    }

    /**
     * Counts the number of constraint elements.
     */
    public function count(): int
    {
        $count = 0;

        foreach ($this->constraints as $constraint) {
            $count += count($constraint);
        }

        return $count;
    }

    /**
     * Returns the nested constraints.
     */
    final protected function constraints(): array
    {
        return $this->constraints;
    }

    /**
     * Returns true if the $constraint needs to be wrapped with braces.
     */
    final protected function constraintNeedsParentheses(Constraint $constraint): bool
    {
        return $this->arity() > 1 && parent::constraintNeedsParentheses($constraint);
    }

    /**
     * Reduces the sub-expression starting at $this by skipping degenerate
     * sub-expression and returns first descendant constraint that starts
     * a non-reducible sub-expression.
     *
     * See Constraint::reduce() for more.
     */
    protected function reduce(): Constraint
    {
        if ($this->arity() === 1 && $this->constraints[0] instanceof Operator) {
            return $this->constraints[0]->reduce();
        }

        return parent::reduce();
    }

    /**
     * Returns string representation of given operand in context of this operator.
     *
     * @param Constraint $constraint operand constraint
     * @param int        $position   position of $constraint in this expression
     */
    private function constraintToString(Constraint $constraint, int $position): string
    {
        $prefix = '';

        if ($position > 0) {
            $prefix = (' ' . $this->operator() . ' ');
        }

        if ($this->constraintNeedsParentheses($constraint)) {
            return $prefix . '( ' . $constraint->toString() . ' )';
        }

        $string = $constraint->toStringInContext($this, $position);

        if ($string === '') {
            $string = $constraint->toString();
        }

        return $prefix . $string;
    }
}
