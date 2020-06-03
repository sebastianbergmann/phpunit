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

abstract class Unary extends Operator
{
    /**
     * @var Constraint
     */
    private $constraint;

    /**
     * @param Constraint|mixed $constraint
     */
    public function __construct($constraint)
    {
        $this->constraint = $this->checkConstraint($constraint);
    }

    /**
     * Returns the number of operands (constraints).
     */
    public function arity(): int
    {
        return 1;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        if ($this->constraintNeedsParentheses($this->constraint)) {
            return $this->operator() . '( ' . $this->constraint->toString() . ' )';
        }

        $string = $this->constraint->toStringInContext($this, 1);

        if ($string === null) {
            return $this->transformString($this->constraint->toString());
        }

        return $string;
    }

    /**
     * Counts the number of constraint elements.
     */
    public function count(): int
    {
        return \count($this->constraint);
    }

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function failureDescription($other): string
    {
        if ($this->constraintNeedsParentheses($this->constraint)) {
            return $this->operator() . '( ' . $this->constraint->failureDescription($other) . ' )';
        }

        $string = $this->constraint->failureDescriptionInContext($this, 1, $other);

        if ($string === null) {
            return $this->transformString($this->constraint->failureDescription($other));
        }

        return $string;
    }

    /**
     * Transforms string returned by the memeber constraint's toString() or
     * failureDescription() such that it reflects constraint's participation in
     * this expression.
     *
     * The method may be overwritten in a subclass to apply default
     * transformation in case the operand constraint does not provide its own
     * custom strings via toStringInContext() or failureDescriptionInContext().
     *
     * @param string $string the string to be transformed
     */
    protected function transformString(string $string): string
    {
        return $string;
    }

    /**
     * Provides access to $this->constraint for subclasses.
     */
    final protected function constraint(): Constraint
    {
        return $this->constraint;
    }
}
