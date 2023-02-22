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

use function array_is_list;
use function gettype;
use function is_array;
use function strtolower;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class IsList extends Constraint
{
    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'is a list';
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        if (!is_array($other)) {
            return false;
        }

        return array_is_list($other);
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     */
    protected function failureDescription(mixed $other): string
    {
        $type = strtolower(gettype($other));

        if ($type === 'double') {
            $type = 'float';
        }

        if ($type === 'resource (closed)') {
            $type = 'closed resource';
        }

        return match ($type) {
            'array', 'integer', 'object' => 'an ' . $type . ' ' . $this->toString(),
            'boolean', 'float', 'null', 'resource', 'string' => 'a ' . $type . ' ' . $this->toString(),
            default => 'a value of ' . $type . ' ' . $this->toString(),
        };
    }
}
