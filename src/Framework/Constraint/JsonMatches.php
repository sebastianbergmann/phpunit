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

use function json_decode;
use function sprintf;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Util\Json;
use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * Asserts whether or not two JSON objects are equal.
 */
final class JsonMatches extends Constraint
{
    /**
     * @var string
     */
    private $value;

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
            $this->value
        );
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * This method can be overridden to implement the evaluation algorithm.
     *
     * @param mixed $other value or object to evaluate
     */
    protected function matches($other): bool
    {
        [$error, $recodedOther] = Json::canonicalize($other);

        if ($error) {
            return false;
        }

        [$error, $recodedValue] = Json::canonicalize($this->value);

        if ($error) {
            return false;
        }

        return $recodedOther == $recodedValue;
    }

    /**
     * Throws an exception for the given compared value and test description.
     *
     * @param mixed  $other       evaluated value or object
     * @param string $description Additional information about the test
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws \SebastianBergmann\RecursionContext\InvalidArgumentException
     * @throws ExpectationFailedException
     *
     * @psalm-return never-return
     */
    protected function fail($other, $description, ComparisonFailure $comparisonFailure = null): void
    {
        if ($comparisonFailure === null) {
            [$error, $recodedOther] = Json::canonicalize($other);

            if ($error) {
                parent::fail($other, $description);
            }

            [$error, $recodedValue] = Json::canonicalize($this->value);

            if ($error) {
                parent::fail($other, $description);
            }

            $comparisonFailure = new ComparisonFailure(
                json_decode($this->value),
                json_decode($other),
                Json::prettify($recodedValue),
                Json::prettify($recodedOther),
                false,
                'Failed asserting that two json values are equal.'
            );
        }

        parent::fail($other, $description, $comparisonFailure);
    }
}
