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
final class InvalidParameterName extends \InvalidArgumentException
{
    public static function notCamelCased(string $value): self
    {
        return new self(\sprintf(
            'Value needs to be a camel-cased key, got "%s" instead.',
            $value,
        ));
    }
}
