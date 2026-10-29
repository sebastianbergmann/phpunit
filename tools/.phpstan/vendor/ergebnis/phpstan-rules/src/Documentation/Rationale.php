<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2026 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/phpstan-rules
 */

namespace Ergebnis\PHPStan\Rules\Documentation;

/**
 * @internal
 */
final class Rationale
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @throws InvalidRationale
     */
    public static function fromString(string $value): self
    {
        if ('' === \trim($value)) {
            throw InvalidRationale::blank();
        }

        if (\trim($value) !== $value) {
            throw InvalidRationale::hasLeadingOrTrailingWhitespace($value);
        }

        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
