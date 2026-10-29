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

namespace Ergebnis\Version\Exception;

final class InvalidVersion extends \InvalidArgumentException
{
    public static function fromString(string $value): self
    {
        return new self(\sprintf(
            'Value "%s" does not appear to be valid.',
            $value,
        ));
    }
}
