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
final class Tip
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @throws InvalidTip
     */
    public static function fromString(string $value): self
    {
        if ('' === \trim($value)) {
            throw InvalidTip::blank();
        }

        if (\trim($value) !== $value) {
            throw InvalidTip::hasLeadingOrTrailingWhitespace($value);
        }

        if (1 === \preg_match('/[\r\n]/', $value)) {
            throw InvalidTip::spansMultipleLines($value);
        }

        $valueWithoutInlineCode = \preg_replace(
            '/`[^`]+`/',
            '',
            $value,
        );

        if (1 === \preg_match('/[`*_\[\]<>#~|\\\\]/', (string) $valueWithoutInlineCode)) {
            throw InvalidTip::usesMarkdownOtherThanInlineCode($value);
        }

        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function toPlainText(): string
    {
        return \str_replace(
            '`',
            '',
            $this->value,
        );
    }
}
