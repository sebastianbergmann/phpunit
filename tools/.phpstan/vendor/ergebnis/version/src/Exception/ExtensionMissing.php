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

final class ExtensionMissing extends \RuntimeException
{
    public static function bcmath(): self
    {
        return new self(\sprintf(
            'The bcmath extension is required to perform calculations for integers that are greater than "%d".',
            \PHP_INT_MAX,
        ));
    }
}
