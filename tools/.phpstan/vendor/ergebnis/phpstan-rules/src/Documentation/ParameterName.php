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
final class ParameterName
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @throws InvalidParameterName
     */
    public static function fromString(string $value): self
    {
        if (1 !== \preg_match('/^[a-z][a-zA-Z0-9]*$/', $value)) {
            throw InvalidParameterName::notCamelCased($value);
        }

        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
