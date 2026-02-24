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

use function array_keys;
use function array_values;
use function is_array;
use function ksort;
use function sort;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Util\Exporter;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
abstract class ArrayComparison extends Constraint
{
    /**
     * @var array<mixed>
     */
    protected readonly array $expected;
    protected readonly bool $keysMatter;
    protected readonly bool $orderMatters;

    /**
     * @param array<mixed> $expected
     */
    public function __construct(array $expected, bool $keysMatter, bool $orderMatters)
    {
        $this->expected     = $expected;
        $this->keysMatter   = $keysMatter;
        $this->orderMatters = $orderMatters;
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
     * @throws ExpectationFailedException
     */
    public function evaluate(mixed $other, string $description = '', bool $returnResult = false): ?bool
    {
        if (!is_array($other)) {
            return false;
        }

        $expected = $this->expected;
        $actual   = $other;

        if ($this->keysMatter && !$this->orderMatters) {
            $expectedKeys = array_keys($expected);
            $actualKeys   = array_keys($actual);
            sort($expectedKeys);
            sort($actualKeys);

            if ($expectedKeys === $actualKeys) {
                sort($expected);
                sort($actual);
            } else {
                ksort($expected);
                ksort($actual);
            }
        }

        if (!$this->keysMatter) {
            $expected = array_values($expected);
            $actual   = array_values($actual);

            if (!$this->orderMatters) {
                sort($expected);
                sort($actual);
            }
        }

        $success = $this->compareArrays($expected, $actual);

        if ($returnResult) {
            return $success;
        }

        if (!$success) {
            $this->fail(
                $other,
                $description,
                new ComparisonFailure(
                    $this->expected,
                    $other,
                    Exporter::export($this->expected),
                    Exporter::export($other),
                ),
            );
        }

        // @codeCoverageIgnoreStart
        return null;
        // @codeCoverageIgnoreEnd
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        if ($this->keysMatter && $this->orderMatters) {
            return 'two arrays are ' . $this->comparisonType();
        }

        if (!$this->keysMatter && !$this->orderMatters) {
            return 'two arrays are ' . $this->comparisonType() . ' while ignoring keys and order';
        }

        if (!$this->keysMatter) {
            return 'two arrays are ' . $this->comparisonType() . ' while ignoring keys';
        }

        return 'two arrays are ' . $this->comparisonType() . ' while ignoring order';
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     */
    protected function failureDescription(mixed $other): string
    {
        return $this->toString();
    }

    /**
     * Compares two arrays using the appropriate comparison method.
     */
    abstract protected function compareArrays(mixed $expected, mixed $actual): bool;

    /**
     * @return 'equal'|'identical'
     */
    abstract protected function comparisonType(): string;
}
