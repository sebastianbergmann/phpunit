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
use ArrayAccess;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class ArrayHasKeys extends Constraint
{
    private array $keys;

    public function __construct(array $keys)
    {
        $this->keys = $keys;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        $keys = implode(', ', $this->keys);

        return 'has the keys ' . $this->exporter()->export($keys);
    }

    /**
     * Evaluates the constraint for parameter $other. Returns false if the
     * constraint is not met, true otherwise.
     */
    protected function matches(mixed $other): bool
    {
        foreach ($this->keys as $key) {
            if (is_array($other)) {
                if (!array_key_exists($key, $other)) {
                    return false;
                }
            } elseif ($other instanceof ArrayAccess) {
                if (!$other->offsetExists($key)) {
                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
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
}
