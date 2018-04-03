<?php
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Framework\Constraint;

use Countable;

/**
 * Constraint that checks whether a variable is empty().
 */
class IsEmpty extends Constraint
{
    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'is empty';
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     */
    protected function matches($other): bool
    {
        if ($other instanceof Countable) {
            return \count($other) === 0;
        }

        return empty($other);
    }

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     */
    protected function failureDescription($other): string
    {
        $type = \gettype($other);

        return \sprintf(
            '%s %s %s',
            $type[0] == 'a' || $type[0] == 'o' ? 'an' : 'a',
            $type,
            $this->toString()
        );
    }
}
