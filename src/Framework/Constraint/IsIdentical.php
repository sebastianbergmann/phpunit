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

use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * Constraint that asserts that one value is identical to another.
 *
 * Identical check is performed with PHP's === operator, the operator is
 * explained in detail at
 * {@url https://php.net/manual/en/types.comparisons.php}.
 * Two values are identical if they have the same value and are of the same
 * type.
 *
 * The expected value is passed in the constructor.
 */
class IsIdentical extends Constraint
{
    /**
     * @var float
     */
    private const EPSILON = 0.0000000001;

    /**
     * @var mixed
     */
    private $value;

    public function __construct($value)
    {
        parent::__construct();

        $this->value = $value;
    }

    /**
     * Evaluates the constraint for parameter $other
     *
     * If $returnResult is set to false (the default), an exception is thrown
     * in case of a failure. null is returned otherwise.
     *
     * If $returnResult is true, the result of the evaluation is returned as
     * a boolean value instead: true in case of success, false in case of a
     * failure.
     *
     * @param mixed  $other        value or object to evaluate
     * @param string $description  Additional information about the test
     * @param bool   $returnResult Whether to return a result or throw an exception
     *
     * @throws ExpectationFailedException
     * @throws SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function evaluate($other, $description = '', $returnResult = false)
    {
        if (\is_float($this->value) && \is_float($other) &&
            !\is_infinite($this->value) && !\is_infinite($other) &&
            !\is_nan($this->value) && !\is_nan($other)) {
            $success = \abs($this->value - $other) < self::EPSILON;
        } else {
            $success = $this->value === $other;
        }

        if ($returnResult) {
            return $success;
        }

        if (!$success) {
            $f = null;

            // if both values are strings, make sure a diff is generated
            if (\is_string($this->value) && \is_string($other)) {
                $f = new ComparisonFailure(
                    $this->value,
                    $other,
                    \sprintf("'%s'", $this->value),
                    \sprintf("'%s'", $other)
                );
            }

            // if both values are array, make sure a diff is generated
            if (\is_array($this->value) && \is_array($other)) {
                $f = new ComparisonFailure(
                    $this->value,
                    $other,
                    $this->exporter->export($this->value),
                    $this->exporter->export($other)
                );
            }

            $this->fail($other, $description, $f);
        }
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @throws SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function toString(): string
    {
        if (\is_object($this->value)) {
            return 'is identical to an object of class "' .
                \get_class($this->value) . '"';
        }

        return 'is identical to ' . $this->exporter->export($this->value);
    }

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other evaluated value or object
     *
     * @throws SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    protected function failureDescription($other): string
    {
        if (\is_object($this->value) && \is_object($other)) {
            return 'two variables reference the same object';
        }

        if (\is_string($this->value) && \is_string($other)) {
            return 'two strings are identical';
        }

        if (\is_array($this->value) && \is_array($other)) {
            return 'two arrays are identical';
        }

        return parent::failureDescription($other);
    }
}
