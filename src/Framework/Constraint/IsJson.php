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

/**
 * Constraint that asserts that a string is valid JSON.
 */
class IsJson extends Constraint
{
    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString(): string
    {
        return 'is valid JSON';
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @param mixed $other value or object to evaluate
     *
     * @return bool
     */
    protected function matches($other): bool
    {
        if ($other === '') {
            return false;
        }

        \json_decode($other);
        if (\json_last_error()) {
            return false;
        }

        return true;
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
     *
     * @return string
     */
    protected function failureDescription($other): string
    {
        if ($other === '') {
            return 'an empty string is valid JSON';
        }

        \json_decode($other);
        $error = JsonMatchesErrorMessageProvider::determineJsonError(
            \json_last_error()
        );

        return \sprintf(
            '%s is valid JSON (%s)',
            $this->exporter->shortenedExport($other),
            $error
        );
    }
}
