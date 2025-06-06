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
final class Invocation
{
    private string $value;

    /**
     * @param non-empty-string $value
     */
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
            'any',
            'first',
            'last',
            'never',
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

    public static function any(): self
    {
        return new self('any');
    }

    public static function first(): self
    {
        return new self('first');
    }

    public static function last(): self
    {
        return new self('last');
    }

    public static function never(): self
    {
        return new self('never');
    }

    /**
     * @return non-empty-string
     */
    public function toString(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
