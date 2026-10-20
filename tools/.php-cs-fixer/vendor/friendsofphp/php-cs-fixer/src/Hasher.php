<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer;

/**
 * @author Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class Hasher
{
    private function __construct()
    {
        // cannot create instance of util. class
    }

    /**
     * @return non-empty-string
     */
    public static function calculate(string $code): string
    {
        return \PHP_VERSION_ID >= 8_01_00
            ? hash('xxh128', $code)
            : md5($code);
    }
}
