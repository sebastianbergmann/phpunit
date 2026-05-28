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

use function file_exists;
use function is_string;
use function sprintf;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class FileExists extends Constraint
{
    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'file exists';
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

        return 'file does not exist';
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

        return file_exists($other);
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     */
    protected function failureDescription(mixed $other): string
    {
        return sprintf(
            'file "%s" exists',
            $this->path($other),
        );
    }

    protected function failureDescriptionInContext(Operator $operator, mixed $role, mixed $other): string
    {
        // @codeCoverageIgnoreStart
        if (!$operator instanceof LogicalNot) {
            return '';
        }
        // @codeCoverageIgnoreEnd

        return sprintf(
            'file "%s" does not exist',
            $this->path($other),
        );
    }

    private function path(mixed $other): string
    {
        if (is_string($other)) {
            return $other;
        }

        return '';
    }
}
