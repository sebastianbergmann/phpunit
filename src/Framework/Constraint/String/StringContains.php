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
use function mb_stripos;
use function mb_strtolower;
use function sprintf;
use function str_contains;
use function strtr;

/**
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise for PHPUnit
 */
final class StringContains extends Constraint
{
    private readonly string $string;
    private readonly bool $ignoreCase;
    private readonly bool $ignoreLineEndings;

    public function __construct(string $string, bool $ignoreCase = false, bool $ignoreLineEndings = false)
    {
        $this->string            = $string;
        $this->ignoreCase        = $ignoreCase;
        $this->ignoreLineEndings = $ignoreLineEndings;
    }

    /**
     * Returns a string representation of the constraint.
     */
    public function toString(): string
    {
        $string = $this->string;

        if ($this->ignoreCase) {
            $string = mb_strtolower($this->string, 'UTF-8');
        }

        if ($this->ignoreLineEndings) {
            $string = $this->normalizeLineEndings($string);
        }

        return sprintf(
            'contains "%s"',
            $string
        );
    }

    /**
     * Evaluates the constraint for parameter $other. Returns true if the
     * constraint is met, false otherwise.
     */
    protected function matches(mixed $other): bool
    {
        if ('' === $this->string) {
            return true;
        }

        if (!is_string($other)) {
            return false;
        }

        if ($this->ignoreLineEndings) {
            $other = $this->normalizeLineEndings($other);
        }

        if ($this->ignoreCase) {
            /*
             * We must use the multi byte safe version so we can accurately compare non latin upper characters with
             * their lowercase equivalents.
             */
            return mb_stripos($other, $this->string, 0, 'UTF-8') !== false;
        }

        /*
         * Use the non multi byte safe functions to see if the string is contained in $other.
         *
         * This function is very fast and we don't care about the character position in the string.
         *
         * Additionally, we want this method to be binary safe so we can check if some binary data is in other binary
         * data.
         */
        return str_contains($other, $this->string);
    }

    private function normalizeLineEndings(string $string): string
    {
        return strtr(
            $string,
            [
                "\r\n" => "\n",
                "\r"   => "\n",
            ]
        );
    }
}
