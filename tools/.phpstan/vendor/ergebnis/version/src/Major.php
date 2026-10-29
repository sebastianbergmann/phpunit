<?php

declare(strict_types=1);

/**
 * Copyright (c) 2023-2026 Andreas Möller
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ergebnis/version
 */

namespace Ergebnis\Version;

final class Major
{
    /**
     * @see https://semver.org/#is-there-a-suggested-regular-expression-regex-to-check-a-semver-string
     * @see https://regex101.com/r/Ly7O1x/3/
     */
    private const REGEX = '/^(?P<major>0|[1-9]\d*)$/';
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @throws Exception\InvalidMajor
     */
    public static function fromInt(int $value): self
    {
        if (0 > $value) {
            throw Exception\InvalidMajor::fromInt($value);
        }

        return new self((string) $value);
    }

    /**
     * @throws Exception\InvalidMajor
     */
    public static function fromString(string $value): self
    {
        if (1 !== \preg_match(self::REGEX, $value)) {
            throw Exception\InvalidMajor::fromString($value);
        }

        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    /**
     * @throws Exception\ExtensionMissing
     */
    public function bump(): self
    {
        $valueCastedToInt = (int) $this->value;

        if (\PHP_INT_MAX === $valueCastedToInt) {
            if (!\extension_loaded('bcmath')) {
                throw Exception\ExtensionMissing::bcmath();
            }

            return new self(\bcadd(
                $this->value,
                '1',
            ));
        }

        return new self((string) ($valueCastedToInt + 1));
    }

    public function compare(self $other): int
    {
        return $this->value <=> $other->value;
    }

    public function isSmallerThan(self $other): bool
    {
        return $this->value < $other->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->value > $other->value;
    }
}
