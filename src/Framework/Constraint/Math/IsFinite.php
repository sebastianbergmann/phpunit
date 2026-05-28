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

use function is_array;
use function is_finite;
use function is_float;
use function is_int;
use function is_object;
use PHPUnit\Util\Exporter;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class IsFinite extends Constraint
{
    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'is finite';
    }

    /**
     * Returns the negated description when this constraint is wrapped in a
     * LogicalNot operator. The guard ensures that LogicalAnd, LogicalOr, and
     * LogicalXor keep using the affirmative toString().
     */
    protected function toStringInContext(Operator $operator, mixed $role): string
    {
        if (!$operator instanceof LogicalNot) {
            return '';
        }

        return 'is not finite';
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        if (!is_float($other) && !is_int($other)) {
            return false;
        }

        return is_finite($other);
    }

    protected function failureDescription(mixed $other): string
    {
        if (is_array($other) || is_object($other)) {
            // @codeCoverageIgnoreStart
            return $this->valueToTypeStringFragment($other) . $this->toString();
            // @codeCoverageIgnoreEnd
        }

        return Exporter::export($other) . ' ' . $this->toString();
    }
}
