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

use function array_key_exists;
use function is_array;
use function is_int;
use function is_string;
use ArrayAccess;
use PHPUnit\Util\Exporter;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class ArrayHasKey extends Constraint
{
    private readonly mixed $key;

    public function __construct(mixed $key)
    {
        $this->key = $key;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'has the key ' . Exporter::export($this->key);
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        if (is_array($other)) {
            if (!is_int($this->key) && !is_string($this->key)) {
                return false;
            }

            return array_key_exists($this->key, $other);
        }

        if ($other instanceof ArrayAccess) {
            return $other->offsetExists($this->key);
        }

        return false;
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     */
    protected function failureDescription(mixed $other): string
    {
        return 'an array ' . $this->toString();
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

        return 'does not have the key ' . Exporter::export($this->key);
    }

    protected function failureDescriptionInContext(Operator $operator, mixed $role, mixed $other): string
    {
        // @codeCoverageIgnoreStart
        if (!$operator instanceof LogicalNot) {
            return '';
        }
        // @codeCoverageIgnoreEnd

        return 'an array ' . $this->toStringInContext($operator, $role);
    }
}
