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

use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\SelfDescribing;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Exporter\Exporter;

/**
 * Abstract base class for constraints which can be applied to any value.
 */
abstract class Constraint implements \Countable, SelfDescribing
{
    /**
     * @var ?Exporter
     */
    private $exporter;

    /**
     * Evaluates the constraint for parameter $other
     *
     * If $returnResult is set to false (the default), an exception is thrown
     * in case of a failure. null is returned otherwise.
     *
     * If $returnResult is true, the result of the evaluation is returned as
     * a boolean value instead: true in case of success, false in case of a
     * failure.
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool
    {
        $success = false;

        if ($this->matches($other)) {
            $success = true;
        }

        if ($returnResult) {
            return $success;
        }

        if (!$success) {
            $this->fail($other, $description);
        }

        return null;
    }

    /**
     * Counts the number of constraint elements.
     */
    public function count(): int
    {
        return 1;
    }

    protected function exporter(): Exporter
    {
        if ($this->exporter === null) {
            $this->exporter = new Exporter;
        }

        return $this->exporter;
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * This method can be overridden to implement the evaluation algorithm.
     *
     * @param mixed $other value or object to evaluate
     * @codeCoverageIgnore
     */
    protected function matches($other): bool
    {
        return false;
    }

    /**
     * Throws an exception for the given compared value and test description
     *
     * @param mixed             $other             evaluated value or object
     * @param string            $description       Additional information about the test
     * @param ComparisonFailure $comparisonFailure
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     *
     * @psalm-return never-return
     */
    protected function fail($other, $description, ComparisonFailure $comparisonFailure = null): void
    {
        $failureDescription = \sprintf(
            'Failed asserting that %s.',
            $this->failureDescription($other)
        );

        $additionalFailureDescription = $this->additionalFailureDescription($other);

        if ($additionalFailureDescription) {
            $failureDescription .= "\n" . $additionalFailureDescription;
        }

        if (!empty($description)) {
            $failureDescription = $description . "\n" . $failureDescription;
        }

        throw new ExpectationFailedException(
            $failureDescription,
            $comparisonFailure
        );
    }

    /**
     * Return additional failure description where needed
     *
     * The function can be overridden to provide additional failure
     * information like a diff
     *
     * @param mixed $other evaluated value or object
     */
    protected function additionalFailureDescription($other): string
    {
        return '';
    }

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * To provide additional failure information additionalFailureDescription
     * can be used.
     *
     * @param mixed $other evaluated value or object
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function failureDescription($other): string
    {
        return $this->exporter()->export($other) . ' ' . $this->toString();
    }

    /**
     * Returns a custom string representation of the constraint object when it
     * appears in context of an $operator expression
     *
     * The purpose of this method is to provide meaningful messages in context
     * of operators such as LogicalNot. Native PHPUnit constraints are supported
     * out of the box by LogicalNot, but externally developed ones had no way to
     * provide correct messages in this context.
     *
     * The method shall return null, if there is no need for customization in the
     * context of given $operator. The $position starts with 0 for connective
     * operators, but for unary operator, such as "not" operator, it equals 1 to
     * denote that this constraint is on the right hand side of the $operator.
     *
     * @param Operator $operator the $operator of the expression
     * @param int      $position position in $operator expression
     */
    protected function toStringInContext(Operator $operator, int $position): ?string
    {
        return null;
    }

    /**
     * Returns the description of the failure when this constraint appears in
     * context of an $operator expression
     *
     * The purpose of this method is to provide meaningful messages in context
     * of operators such as LogicalNot. Native PHPUnit constraints are supported
     * out of the box by LogicalNot, but externally developed ones had no way to
     * provide correct messages in this context.
     *
     * The method shall return null, if there is no need for customization in
     * the context of given $operator. The $position starts with 0 for
     * connective operators, but for unary operator, such as "not" operator, it
     * equals 1 to denote that this constraint is on the right hand side of the
     * $operator.
     *
     * @param Operator $operator the $operator of the expression
     * @param int      $position position in $operator expression
     * @param mixed    $other    evaluated value or object
     */
    protected function failureDescriptionInContext(Operator $operator, int $position, $other): ?string
    {
        $string = $this->toStringInContext($operator, $position);

        if ($string === null) {
            return null;
        }

        return $this->exporter()->export($other) . ' ' . $string;
    }

    /**
     * Reduces the sub-expression starting at $this by skipping degenerate
     * sub-expression and returns first descendant constraint that starts
     * a non-reducible sub-expression
     *
     * Returns $this for terminal constraints and for operators that start
     * non-reducible sub-expression, or the nearest descendant of $this that
     * starts a non-reducible sub-expression.
     *
     * A constraint expression may be modelled as a tree with non-terminal
     * nodes (operators) and terminal nodes. For example:
     *
     *      LogicalOr           (operator, non-terminal)
     *      + LogicalAnd        (operator, non-terminal)
     *      | + IsType('int')   (terminal)
     *      | + GreaterThan(10) (terminal)
     *      + LogicalNot        (operator, non-terminal)
     *        + IsType('array') (terminal)
     *
     * A degenerate sub-expression is a part of the tree, that effectively does
     * not contribute to the evaluation of the expression it appears in. An example
     * of degenerate sub-expression is a BinaryOperator constructed with single
     * operand or nested BinaryOperators, each with single operand. An
     * expression involving a degenerate sub-expression is equivalent to a
     * reduced expression with the degenerate sub-expression removed, for example
     *
     *      LogicalAnd          (operator)
     *      + LogicalOr         (degenerate operator)
     *      | + LogicalAnd      (degenerate operator)
     *      |   + IsType('int') (terminal)
     *      + GreaterThan(10)   (terminal)
     *
     * is equivalent to
     *
     *      LogicalAnd          (operator)
     *      + IsType('int')     (terminal)
     *      + GreaterThan(10)   (terminal)
     *
     * because the subexpression
     *
     *      + LogicalOr
     *        + LogicalAnd
     *          + -
     *
     * is degenerate. Calling reduce() on the LogicalOr object above, as well
     * as on LogicalAnd, shall return the IsType('int') instance.
     *
     * Other specific reductions can be implemented, for example cascade of
     * LogicalNot operators
     *
     *      + LogicalNot
     *        + LogicalNot
     *          +LogicalNot
     *           + IsTrue
     *
     * can be reduced to
     *
     *      LogicalNot
     *      + IsTrue
     */
    protected function reduce(): self
    {
        return $this;
    }
}
