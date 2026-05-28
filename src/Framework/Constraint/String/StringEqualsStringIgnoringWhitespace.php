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

use function assert;
use function is_string;
use function preg_replace;
use function sprintf;
use function trim;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class StringEqualsStringIgnoringWhitespace extends Constraint
{
    private readonly string $string;

    public function __construct(string $string)
    {
        $this->string = $this->normalizeWhitespace($string);
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return 'is equal to ' . $this->valueAsString();
    }

    /**
     * Returns the negated description when this constraint is wrapped in a
     * LogicalNot operator. Authoring the negation here keeps the expected value
     * out of the negation entirely. The guard ensures that LogicalAnd,
     * LogicalOr, and LogicalXor keep using the affirmative toString().
     */
    protected function toStringInContext(Operator $operator, mixed $role): string
    {
        if (!$operator instanceof LogicalNot) {
            return '';
        }

        return 'is not equal to ' . $this->valueAsString();
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        if (!is_string($other)) {
            return false;
        }

        return $this->string === $this->normalizeWhitespace($other);
    }

    private function valueAsString(): string
    {
        return sprintf(
            '"%s" ignoring whitespace',
            $this->string,
        );
    }

    private function normalizeWhitespace(string $string): string
    {
        $normalized = preg_replace('/\s+/u', ' ', $string);

        assert($normalized !== null);

        return trim($normalized);
    }
}
