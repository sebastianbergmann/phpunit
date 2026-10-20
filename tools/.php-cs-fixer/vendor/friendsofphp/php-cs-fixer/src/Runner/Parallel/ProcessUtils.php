<?php

declare(strict_types=1);

/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This file was copied (and slightly modified) from Symfony:
 * - https://github.com/symfony/symfony/blob/3.4/src/Symfony/Component/Process/ProcessUtils.php#L41
 * - (c) Fabien Potencier <fabien@symfony.com>
 * - For the full copyright and license information, please see https://github.com/symfony/symfony/blob/3.4/src/Symfony/Component/Process/LICENSE
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace PhpCsFixer\Runner\Parallel;

/**
 * @internal
 *
 * @no-named-arguments Parameter names are not covered by the backward compatibility promise.
 */
final class ProcessUtils
{
    /**
     * This class should not be instantiated.
     */
    private function __construct() {}

    /**
     * Escapes a string to be used as a shell argument.
     *
     * @param string $argument The argument that will be escaped
     *
     * @return string The escaped argument
     */
    public static function escapeArgument(string $argument): string
    {
        // Fix for PHP bug #43784 escapeshellarg removes % from given string
        // Fix for PHP bug #49446 escapeshellarg doesn't work on Windows
        // @see https://bugs.php.net/43784
        // @see https://bugs.php.net/49446
        if ('\\' === \DIRECTORY_SEPARATOR) {
            if ('' === $argument) {
                return escapeshellarg($argument);
            }

            $escapedArgument = '';
            $quote = false;
            foreach (preg_split('/(")/', $argument, -1, \PREG_SPLIT_NO_EMPTY | \PREG_SPLIT_DELIM_CAPTURE) as $part) { // @phpstan-ignore foreach.nonIterable
                if ('"' === $part) {
                    $escapedArgument .= '\"';
                } elseif (self::isSurroundedBy($part, '%')) {
                    // Avoid environment variable expansion
                    $escapedArgument .= '^%"'.substr($part, 1, -1).'"^%';
                } else {
                    // escape trailing backslash
                    if ('\\' === substr($part, -1)) {
                        $part .= '\\';
                    }
                    $quote = true;
                    $escapedArgument .= $part;
                }
            }
            if ($quote) {
                $escapedArgument = '"'.$escapedArgument.'"';
            }

            return $escapedArgument;
        }

        return "'".str_replace("'", "'\\''", $argument)."'";
    }

    private static function isSurroundedBy(string $arg, string $char): bool
    {
        return 2 < \strlen($arg) && $char === $arg[0] && $char === $arg[\strlen($arg) - 1];
    }
}
