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

use function is_string;
use function str_starts_with;
use PHPUnit\Framework\EmptyStringException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class StringStartsWith extends Constraint
{
    private readonly string $prefix;

    /**
     * @throws EmptyStringException
     */
    public function __construct(string $prefix)
    {
        if ($prefix === '') {
            throw new EmptyStringException;
        }

        $this->prefix = $prefix;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'starts with "' . $this->prefix . '"';
    }

    /**
     * Returns the negated description when this constraint is wrapped in a
     * LogicalNot operator. Authoring the negation here keeps the prefix out of
     * the negation entirely. The guard ensures that LogicalAnd, LogicalOr, and
     * LogicalXor keep using the affirmative toString().
     */
    protected function toStringInContext(Operator $operator, mixed $role): string
    {
        if (!$operator instanceof LogicalNot) {
            return '';
        }

        return 'does not start with "' . $this->prefix . '"';
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        if (!is_string($other)) {
            return false;
        }

        return str_starts_with($other, $this->prefix);
    }
}
