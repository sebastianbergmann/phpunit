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
final class InvalidDescription extends \InvalidArgumentException
{
    public static function blank(): self
    {
        return new self('Value can not be blank.');
    }

    public static function hasLeadingOrTrailingWhitespace(string $value): self
    {
        return new self(\sprintf(
            'Value can not have leading or trailing whitespace, got "%s" instead.',
            $value,
        ));
    }
}
