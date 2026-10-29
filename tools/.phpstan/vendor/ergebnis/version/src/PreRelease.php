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

final class PreRelease
{
    /**
     * @see https://semver.org/#is-there-a-suggested-regular-expression-regex-to-check-a-semver-string
     * @see https://regex101.com/r/Ly7O1x/3/
     */
    private const REGEX = '/^(?P<prerelease>(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*)(?:\.(?:0|[1-9]\d*|\d*[a-zA-Z-][0-9a-zA-Z-]*))*)$/';
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @throws Exception\InvalidPreRelease
     */
    public static function fromString(string $value): self
    {
        if (1 !== \preg_match(self::REGEX, $value)) {
            throw Exception\InvalidPreRelease::fromString($value);
        }

        return new self($value);
    }

    public static function empty(): self
    {
        return new self('');
    }

    public function toString(): string
    {
        return $this->value;
    }

    /**
     * @see https://semver.org/#spec-item-11
     */
    public function compare(self $other): int
    {
        $identifiers = \explode(
            '.',
            $this->value,
        );

        $otherIdentifiers = \explode(
            '.',
            $other->value,
        );

        $maxCount = \max(
            \count($identifiers),
            \count($otherIdentifiers),
        );

        for ($i = 0; $maxCount > $i; ++$i) {
            if (!\array_key_exists($i, $identifiers)) {
                return -1;
            }

            $identifier = $identifiers[$i];

            if (!\array_key_exists($i, $otherIdentifiers)) {
                return 1;
            }

            $otherIdentifier = $otherIdentifiers[$i];

            if ($identifier === $otherIdentifier) {
                continue;
            }

            if (self::isNumericIdentifier($identifier)) {
                if (self::isNumericIdentifier($otherIdentifier)) {
                    return $identifier <=> $otherIdentifier;
                }

                return -1;
            }

            if (self::isNumericIdentifier($otherIdentifier)) {
                return 1;
            }

            return \strnatcmp(
                $identifier,
                $otherIdentifier,
            );
        }

        return 0;
    }

    public function isSmallerThan(self $other): bool
    {
        return -1 === $this->compare($other);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function isGreaterThan(self $other): bool
    {
        return 1 === $this->compare($other);
    }

    private static function isNumericIdentifier(string $value): bool
    {
        return 1 === \preg_match('/^\d+$/', $value);
    }
}
