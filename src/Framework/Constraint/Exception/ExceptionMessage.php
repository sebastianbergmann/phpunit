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

use function sprintf;
use function str_contains;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class ExceptionMessage extends Constraint
{
    private string $expectedMessage;

    public function __construct(string $expected)
    {
        $this->expectedMessage = $expected;
    }

    public function toString(): string
    {
        if ($this->expectedMessage === '') {
            return 'exception message is empty';
        }

        return 'exception message contains ';
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        if ($this->expectedMessage === '') {
            return $other->getMessage() === '';
        }

        return str_contains((string) $other->getMessage(), $this->expectedMessage);
    }

    /**
     * Returns the description of the failure.
     *
     * The beginning of failure messages is "Failed asserting that" in most
     * cases. This method should return the second part of that sentence.
     */
    protected function failureDescription(mixed $other): string
    {
        if ($this->expectedMessage === '') {
            return sprintf(
                "exception message is empty but is '%s'",
                $other->getMessage()
            );
        }

        return sprintf(
            "exception message '%s' contains '%s'",
            $other->getMessage(),
            $this->expectedMessage
        );
    }
}
