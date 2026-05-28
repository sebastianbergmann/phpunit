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

use function is_string;
use function json_decode;
use function sprintf;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Util\InvalidJsonException;
use PHPUnit\Util\Json;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class JsonMatches extends Constraint
{
    private readonly string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Returns a string representation of the object.
     */
    public function toString(): string
    {
        return sprintf(
            'matches JSON string "%s"',
            $this->value,
        );
    }

    /**
     * Returns the negated description when this constraint is wrapped in a
     * LogicalNot operator. Authoring the negation here keeps the expected JSON
     * out of the negation entirely. The guard ensures that LogicalAnd,
     * LogicalOr, and LogicalXor keep using the affirmative toString().
     */
    protected function toStringInContext(Operator $operator, mixed $role): string
    {
        if (!$operator instanceof LogicalNot) {
            return '';
        }

        return sprintf(
            'does not match JSON string "%s"',
            $this->value,
        );
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * This method can be overridden to implement the evaluation algorithm.
     */
    protected function matches(mixed $other): bool
    {
        if (!is_string($other)) {
            return false;
        }

        [$error, $recodedOther] = Json::canonicalize($other);

        if ($error) {
            return false;
        }

        [$error, $recodedValue] = Json::canonicalize($this->value);

        if ($error) {
            return false;
        }

        return $recodedOther === $recodedValue;
    }

    /**
     * Throws an exception for the given compared value and test description.
     *
     * @throws ExpectationFailedException
     * @throws InvalidJsonException
     */
    protected function fail(mixed $other, string $description, ?ComparisonFailure $comparisonFailure = null): never
    {
        if ($comparisonFailure === null && is_string($other)) {
            [$error, $recodedOther] = Json::canonicalize($other);

            if ($error || $recodedOther === null) {
                parent::fail($other, $description);
            }

            [$error, $recodedValue] = Json::canonicalize($this->value);

            if ($error || $recodedValue === null) {
                parent::fail($other, $description);
            }

            $comparisonFailure = new ComparisonFailure(
                json_decode($this->value),
                json_decode($other),
                Json::prettify($recodedValue),
                Json::prettify($recodedOther),
                'Failed asserting that two json values are equal.',
            );
        }

        parent::fail($other, $description, $comparisonFailure);
    }
}
