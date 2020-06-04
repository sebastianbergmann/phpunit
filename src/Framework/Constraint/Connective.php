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

/**
 * Abstract base class for connectives (n-ary) logical operators.
 *
 * Infix notation of the operator is assumed.
 */
abstract class Connective extends Operator
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
        $this->constraints = \array_map(function ($constraint): Constraint {
            return $this->checkConstraint($constraint);
        }, \array_values($constraints));
    }

    /**
     * Returns the number of operands (constraints).
     */
    final public function arity(): int
    {
        return \count($this->constraints);
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        $text = '';

        foreach ($this->constraints as $key => $constraint) {
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
            $count += \count($constraint);
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
     * Returns string representation of given operand in context of this operator
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

        return $prefix . ($string ?? $constraint->toString());
    }
}
