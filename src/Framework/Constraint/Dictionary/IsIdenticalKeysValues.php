<?php

declare(strict_types=1);

namespace PHPUnit\Framework\Constraint\Dictionary;

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\ExpectationFailedException;
use SebastianBergmann\Comparator\ComparisonFailure;
use SebastianBergmann\Exporter\Exporter;

final class IsIdenticalKeysValues extends Constraint
{
    private readonly mixed $value;

    public function __construct(mixed $value)
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
     * @throws ExpectationFailedException
     */
    public function evaluate(mixed $other, string $description = '', bool $returnResult = false): ?bool
    {
        assert(is_array($this->value));
        assert(is_array($other));

        // cribbed from `src/Framework/Constraint/Equality/IsEqualCanonicalizing.php`
        try {
            $this->compareDictionary($this->value, $other);
        } catch (ComparisonFailure $f) {
            if ($returnResult) {
                return false;
            }
            throw new ExpectationFailedException(
                trim($description . "\n" . $f->getMessage()),
                $f
            );
        }
        return true;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'is identical to ' . (new Exporter)->export($this->value);
    }

    /**
     * cribbed from `vendor/sebastian/comparator/src/ArrayComparator.php`
     * This potentially should be a dictionarycomparator or type-strict arraycomparator
     */
    private function compareDictionary(array $expected, array $actual, array &$processed = []): void
    {
        $remaining = $actual;
        $actualAsString = "Array (\n";
        $expectedAsString = "Array (\n";
        $equal = true;
        $exporter = new Exporter;

        foreach ($expected as $key => $value) {
            unset($remaining[$key]);

            if (!array_key_exists($key, $actual)) {
                $expectedAsString .= sprintf(
                    "    %s => %s\n",
                    $exporter->export($key),
                    $exporter->shortenedExport($value),
                );
                $equal = false;
                continue;
            }

            try {
                switch (true) {
                    // type mismatch, expected array, got something else
                    case is_array($value) && !is_array($actual[$key]):
                        throw new ComparisonFailure(
                            $value,
                            $actual[$key],
                            $exporter->export($value),
                            $exporter->export($actual[$key]),
                        );

                    // expected array, got array
                    case is_array($value) && is_array($actual[$key]):
                        $this->compareDictionary($value, $actual[$key]);
                        break;

                    // type mismatch, expected object, got something else
                    case is_object($value) && !is_object($actual[$key]):
                        throw new ComparisonFailure(
                            $value,
                            $actual[$key],
                            $exporter->export($value),
                            $exporter->export($actual[$key]),
                        );

                    // type mismatch, expected object, got object
                    case is_object($value) && is_object($actual[$key]):
                        $this->compareObjects($value, $actual[$key], $processed);
                        break;

                    // both are not array, both are not objects, strict comparison check
                    default:
                        if ($value === $actual[$key]) {
                            continue 2;
                        }
                        throw new ComparisonFailure(
                            $value,
                            $actual[$key],
                            $exporter->export($value),
                            $exporter->export($actual[$key]),
                        );
                }

                $expectedAsString .= sprintf(
                    "    %s => %s\n",
                    $exporter->export($key),
                    $exporter->shortenedExport($value),
                );
                $actualAsString .= sprintf(
                    "    %s => %s\n",
                    $exporter->export($key),
                    $exporter->shortenedExport($actual[$key]),
                );
            } catch (ComparisonFailure $e) {
                $expectedAsString .= sprintf(
                    "    %s => %s\n",
                    $exporter->export($key),
                    $e->getExpectedAsString() !== '' ? $this->indent(
                        $e->getExpectedAsString()
                    ) : $exporter->shortenedExport($e->getExpected()),
                );
                $actualAsString .= sprintf(
                    "    %s => %s\n",
                    $exporter->export($key),
                    $e->getActualAsString() !== '' ? $this->indent(
                        $e->getActualAsString()
                    ) : $exporter->shortenedExport($e->getActual()),
                );
                $equal = false;
            }
        }

        foreach ($remaining as $key => $value) {
            $actualAsString .= sprintf(
                "    %s => %s\n",
                $exporter->export($key),
                $exporter->shortenedExport($value),
            );
            $equal = false;
        }

        $expectedAsString .= ')';
        $actualAsString .= ')';

        if (!$equal) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                $expectedAsString,
                $actualAsString,
                'Failed asserting that two arrays are equal.',
            );
        }
    }

    /**
     * cribbed from `vendor/sebastian/comparator/src/ObjectComparator.php`
     * this potentially should be a type-strict objectcomparator
     */
    private function compareObjects(object $expected, object $actual, array &$processed = [])
    {
        if ($actual::class !== $expected::class) {
            $exporter = new Exporter;

            throw new ComparisonFailure(
                $expected,
                $actual,
                $exporter->export($expected),
                $exporter->export($actual),
                sprintf(
                    '%s is not instance of expected class "%s".',
                    $exporter->export($actual),
                    $expected::class,
                ),
            );
        }

        // don't compare twice to allow for cyclic dependencies
        if (in_array([$actual, $expected], $processed, true) ||
            in_array([$expected, $actual], $processed, true)) {
            return;
        }

        $processed[] = [$actual, $expected];
        if ($actual === $expected) {
            return;
        }
        try {
            $this->compareDictionary($this->toArray($expected), $this->toArray($actual), $processed);
        } catch (ComparisonFailure $e) {
            throw new ComparisonFailure(
                $expected,
                $actual,
                // replace "Array" with "MyClass object"
                substr_replace($e->getExpectedAsString(), $expected::class . ' Object', 0, 5),
                substr_replace($e->getActualAsString(), $actual::class . ' Object', 0, 5),
                'Failed asserting that two objects are equal.',
            );
        }
    }

    /**
     * cribbed from `vendor/sebastian/comparator/src/ObjectComparator.php`
     */
    private function toArray(object $object): array
    {
        return (new Exporter)->toArray($object);
    }

    /**
     * cribbed from `vendor/sebastian/comparator/src/ArrayComparator.php`
     */
    private function indent(string $lines): string
    {
        return trim(str_replace("\n", "\n    ", $lines));
    }
}
