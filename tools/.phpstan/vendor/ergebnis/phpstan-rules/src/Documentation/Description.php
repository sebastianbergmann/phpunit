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
final class Description
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @throws InvalidDescription
     */
    public static function fromString(string $value): self
    {
        if ('' === \trim($value)) {
            throw InvalidDescription::blank();
        }

        if (\trim($value) !== $value) {
            throw InvalidDescription::hasLeadingOrTrailingWhitespace($value);
        }

        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
