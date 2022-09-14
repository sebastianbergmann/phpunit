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

use function get_class;
use function is_array;
use function is_object;
use function is_string;
use function sprintf;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class IsIdentical extends Constraint
{
    /**
     * @var mixed
     */
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Evaluates the constraint for parameter $other.
     *
     * If $returnResult is set to false (the default), an exception is thrown
     * in case of a failure. null is returned otherwise.
     *
     * If $returnResult is true, the result of the evaluation is returned as
     * a boolean value instead: true in case of success, false in case of a
     * failure.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     */
    public function evaluate($other, string $description = '', bool $returnResult = false): ?bool
    {
        $success = $this->value === $other;

        if ($returnResult) {
            return $success;
        }

        if (!$success) {
            $f = null;

            // if both values are strings, make sure a diff is generated
            if (is_string($this->value) && is_string($other)) {
                $f = new ComparisonFailure(
                    $this->value,
                    $other,
                    sprintf("'%s'", $this->value),
                    sprintf("'%s'", $other)
                );
            }

            // if both values are array, make sure a diff is generated
            if (is_array($this->value) && is_array($other)) {
                $f = new ComparisonFailure(
                    $this->value,
                    $other,
                    $this->exporter()->export($this->value),
                    $this->exporter()->export($other)
                );
            }

            $this->fail($other, $description, $f);
        }

        return null;
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function toString(): string
    {
        if (is_object($this->value)) {
            return 'is identical to an object of class "' .
                get_class($this->value) . '"';
        }

        return 'is identical to ' . $this->exporter()->export($this->value);
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function failureDescription($other): string
    {
        if (is_object($this->value) && is_object($other)) {
            return 'two variables reference the same object';
        }

        if (is_string($this->value) && is_string($other)) {
            return 'two strings are identical';
        }

        if (is_array($this->value) && is_array($other)) {
            return 'two arrays are identical';
        }

        return parent::failureDescription($other);
    }
}
