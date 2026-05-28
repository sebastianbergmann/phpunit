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
use function preg_last_error_msg;
use function preg_match;
use function sprintf;
use PHPUnit\Framework\Exception as FrameworkException;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class RegularExpression extends Constraint
{
    private readonly string $pattern;

    public function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        return sprintf(
            'matches PCRE pattern "%s"',
            $this->pattern,
        );
    }

    /**
     * Returns the negated description when this constraint is wrapped in a
     * LogicalNot operator. Authoring the negation here keeps the pattern out of
     * the negation entirely. The guard ensures that LogicalAnd, LogicalOr, and
     * LogicalXor keep using the affirmative toString().
     */
    protected function toStringInContext(Operator $operator, mixed $role): string
    {
        if (!$operator instanceof LogicalNot) {
            return '';
        }

        return sprintf(
            'does not match PCRE pattern "%s"',
            $this->pattern,
        );
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     *
     * @throws FrameworkException
     */
    protected function matches(mixed $other): bool
    {
        if (!is_string($other)) {
            return false;
        }

        $matches = @preg_match($this->pattern, $other);

        if ($matches === false) {
            throw new FrameworkException(
                sprintf(
                    'Regular expression cannot be matched: %s',
                    preg_last_error_msg(),
                ),
            );
        }

        return $matches > 0;
    }
}
