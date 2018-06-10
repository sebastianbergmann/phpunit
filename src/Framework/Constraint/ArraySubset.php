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
     * @var iterable
     */
    private $subset;

    /**
     * @var bool
     */
    private $strict;

    public function __construct(iterable $subset, bool $strict = false)
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
     * @param mixed  $other        value or object to evaluate
     * @param string $description  Additional information about the test
     * @param bool   $returnResult Whether to return a result or throw an exception
     *
     * @throws ExpectationFailedException
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function evaluate($other, $description = '', $returnResult = false)
    {
        //type cast $other & $this->subset as an array to allow
        //support in standard array functions.
        $other        = $this->toArray($other);
        $this->subset = $this->toArray($this->subset);

        $intersect = $this->arrayIntersectRecursive($other, $this->subset);

        $this->deepSort($intersect);
        $this->deepSort($this->subset);

        $result = $this->compare($intersect, $this->subset);

        if ($returnResult) {
            return $result;
        }

        if (!$result) {
            $f = new ComparisonFailure(
                $this->subset,
                $other,
                \print_r($this->subset, true),
                \print_r($other, true)
            );

            $this->fail($other, $description, $f);
        }
    }

    /**
     * Returns a string representation of the constraint.
     *
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     */
    public function toString(): string
    {
        return 'has the subset ' . $this->exporter->export($this->subset);
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
     */
    protected function failureDescription($other): string
    {
        return 'an array ' . $this->toString();
    }

    private function toArray(iterable $other): array
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

    private function isAssociative(array $array): bool
    {
        return \array_reduce(
            \array_keys($array),
            function (bool $carry, $key): bool {
                return $carry || \is_string($key);
            },
            false
        );
    }

    private function compare($first, $second): bool
    {
        return $this->strict ? $first === $second : $first == $second;
    }

    private function deepSort(array &$array): void
    {
        foreach ($array as &$value) {
            if (\is_array($value)) {
                $this->deepSort($value);
            }
        }

        unset($value);

        if ($this->isAssociative($array)) {
            \ksort($array);
        } else {
            \sort($array);
        }
    }

    private function arrayIntersectRecursive(array $array, array $subset): array
    {
        $intersect = [];

        if ($this->isAssociative($subset)) {
            // If the subset is an associative array, get the intersection while
            // preserving the keys.
            foreach ($subset as $key => $subset_value) {
                if (\array_key_exists($key, $array)) {
                    $array_value = $array[$key];

                    if (\is_array($subset_value) && \is_array($array_value)) {
                        $intersect[$key] = $this->arrayIntersectRecursive($array_value, $subset_value);
                    } elseif ($this->compare($subset_value, $array_value)) {
                        $intersect[$key] = $array_value;
                    }
                }
            }
        } else {
            // If the subset is an indexed array, loop over all entries in the
            // haystack and check if they match the ones in the subset.
            foreach ($array as $array_value) {
                if (\is_array($array_value)) {
                    foreach ($subset as $key => $subset_value) {
                        if (\is_array($subset_value)) {
                            $recursed = $this->arrayIntersectRecursive($array_value, $subset_value);

                            if (!empty($recursed)) {
                                $intersect[$key] = $recursed;

                                break;
                            }
                        }
                    }
                } else {
                    foreach ($subset as $key => $subset_value) {
                        if (!\is_array($subset_value) && $this->compare($subset_value, $array_value)) {
                            $intersect[$key] = $array_value;

                            break;
                        }
                    }
                }
            }
        }

        return $intersect;
    }
}
