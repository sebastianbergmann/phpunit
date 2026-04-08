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
        return sprintf(
            'is equal to "%s" ignoring whitespace',
            $this->string,
        );
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        return $this->string === $this->normalizeWhitespace((string) $other);
    }

    private function normalizeWhitespace(string $string): string
    {
        return trim(preg_replace('/\s+/u', ' ', $string));
    }
}
