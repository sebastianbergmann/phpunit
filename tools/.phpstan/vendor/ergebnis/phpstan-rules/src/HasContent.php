<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2025 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/phpstan-rules
 */

namespace Ergebnis\PHPStan\Rules;

/**
 * @internal
 */
final class HasContent
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function fromString(string $value): self
    {
        $values = [
            'maybe',
            'no',
            'yes',
        ];

        if (!\in_array($value, $values, true)) {
            throw new \InvalidArgumentException(\sprintf(
                'Value needs to be one of "%s", got "%s" instead.',
                \implode('", "', $values),
                $value,
            ));
        }

        return new self($value);
    }

    public static function maybe(): self
    {
        return new self('maybe');
    }

    public static function no(): self
    {
        return new self('no');
    }

    public static function yes(): self
    {
        return new self('yes');
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
