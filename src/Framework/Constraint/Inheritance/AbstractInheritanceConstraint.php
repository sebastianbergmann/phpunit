<?php

declare(strict_types=1);
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
 * Abstract base class for inheritance constraints (ExtendsClass,
 * ImplementsInterface, UsesTrait, etc.).
 *
 * @psalm-template Subclass of AbstractInheritanceConstraint
 */
abstract class AbstractInheritanceConstraint extends Constraint
{
    /**
     * @var string
     * @psalm-readonly
     */
    private $expected;

    /**
     * Initializes the constraint.
     */
    protected function __construct(string $expected)
    {
        $this->expected = $expected;
    }

    /**
     * Returns a string representation of the constraint.
     */
    final public function toString(): string
    {
        return sprintf('%s %s', $this->verb(), $this->expected);
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     */
    final public function matches($other): bool
    {
        if (is_object($other)) {
            $other = get_class($other);
        }

        if (!is_string($other) || !$this->supportsActual($other)) {
            return false;
        }

        return in_array($this->expected, $this->inheritance($other), true);
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     */
    final public function failureDescription($other): string
    {
        return $this->short($other) . ' ' . $this->toString();
    }

    /**
     * Returns short description of what we examine, e.g. ``'impements interface'``.
     */
    abstract protected function verb(bool $negated = false): string;

    /**
     * Returns an array of "inherited classes" -- eiher interfaces *$class*
     * implements, parent classes it extends or traits it uses, depending on
     * the actual implementation of this constraint.
     */
    abstract protected function inheritance(string $class): array;

    /**
     * Checks if *$string* may be used as an argument to ``inheritance()``.
     */
    abstract protected function supportsActual(string $string): bool;

    /**
     * Returns a custom string representation of the constraint object when it
     * appears in context of an $operator expression.
     *
     * The purpose of this method is to provide meaningful descriptive string
     * in context of operators such as LogicalNot. Native PHPUnit constraints
     * are supported out of the box by LogicalNot, but externally developed
     * ones had no way to provide correct strings in this context.
     *
     * The method shall return empty string, when it does not handle
     * customization by itself.
     *
     * @param Operator $operator the $operator of the expression
     * @param mixed    $role     role of $this constraint in the $operator expression
     */
    final protected function toStringInContext(Operator $operator, $role): string
    {
        if ($operator instanceof LogicalNot) {
            return sprintf('%s %s', $this->verb(true), $this->expected);
        }

        return '';
    }

    /**
     * Returns the description of the failure when this constraint appears in
     * context of an $operator expression.
     *
     * The purpose of this method is to provide meaningful failue description
     * in context of operators such as LogicalNot. Native PHPUnit constraints
     * are supported out of the box by LogicalNot, but externally developed
     * ones had no way to provide correct messages in this context.
     *
     * The method shall return empty string, when it does not handle
     * customization by itself.
     *
     * @param Operator $operator the $operator of the expression
     * @param mixed    $role     role of $this constraint in the $operator expression
     * @param mixed    $other    evaluated value or object
     */
    final protected function failureDescriptionInContext(Operator $operator, $role, $other): string
    {
        $string = $this->toStringInContext($operator, $role);

        if ('' === $string) {
            return '';
        }

        return $this->short($other) . ' ' . $string;
    }

    /**
     * Returns short representation of $subject for failureDescription().
     *
     * @param mixed $subject
     */
    private function short($subject): string
    {
        if (is_object($subject)) {
            $subject = 'object ' . get_class($subject);
        } elseif (!is_string($subject) || !$this->supportsActual($subject)) {
            $subject = $this->exporter()->export($subject);
        }

        return $subject;
    }
}

// vim: syntax=php sw=4 ts=4 et:
