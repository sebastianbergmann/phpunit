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
 * Constraint that asserts that the array it is evaluated for has a specified subset.
 *
 * Uses array_replace_recursive() to check if a key value subset is part of the
 * subject array.
 */
class ArraySubset extends Constraint
{
    /**
     * @var array|\Traversable
     */
    protected $subset;

    /**
     * @var bool
     */
    protected $strict;

    /**
     * @param array|\Traversable $subset
     * @param bool               $strict Check for object identity
     */
    public function __construct($subset, $strict = false)
    {
        parent::__construct();

        $this->strict = $strict;
        $this->subset = $subset;
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
     * @param mixed  $other        Value or object to evaluate.
     * @param string $description  Additional information about the test
     * @param bool   $returnResult Whether to return a result or throw an exception
     *
     * @return mixed
     *
     * @throws ExpectationFailedException
     */
    public function evaluate($other, $description = '', $returnResult = false)
    {
        //type cast $other & $this->subset as an array to allow
        //support in standard array functions.
        $other        = $this->toArray($other);
        $this->subset = $this->toArray($this->subset);

        $patched = \array_replace_recursive($other, $this->subset);

        if ($this->strict) {
            $result = $other === $patched;
        } else {
            $result = $other == $patched;
        }

        if ($returnResult) {
            return $result;
        }

        if (!$result) {
            $f = new ComparisonFailure(
                $patched,
                $other,
                \var_export($patched, true),
                \var_export($other, true)
            );

            $this->fail($other, $description, $f);
        }
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @return string
     */
    public function toString()
    {
        return 'has the subset ' . $this->exporter->export($this->subset);
    }

    /**
     * Returns the description of the failure
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     *
     * @param mixed $other Evaluated value or object.
     *
     * @return string
     */
    protected function failureDescription($other)
    {
        return 'an array ' . $this->toString();
    }

    /**
     * @param array|\Traversable $other
     *
     * @return array
     */
    private function toArray($other)
    {
        if (\is_array($other)) {
            return $other;
        }

        if ($other instanceof \ArrayObject) {
            return $other->getArrayCopy();
        }

        if ($other instanceof \Traversable) {
            return \iterator_to_array($other);
        }

        // Keep BC even if we know that array would not be the expected one
        return (array) $other;
    }
}
