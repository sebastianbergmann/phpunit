#!/usr/bin/env php
<?php declare(strict_types=1);

/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
if (!isset($argv[1]) || !\file_exists($argv[1])) {
    \fwrite(
        \STDERR,
        \sprintf(
            '%s /path/to/phpunit-*.phar' . \PHP_EOL,
            $argv[0]
        )
    );

    exit(1);
}

if (!str_contains_between(\file_get_contents($argv[1]), 'PHPUnitPHAR', '$classes = [', '];') &&
    !str_contains_between(\file_get_contents($argv[1]), 'phpunitphar', '$classes = [', '];')) {
    \fwrite(\STDERR, 'PHAR is not scoped' . \PHP_EOL);

    exit(1);
}

\fwrite(\STDOUT, 'PHAR is scoped' . \PHP_EOL);

/**
 * @param non-empty-string $haystack
 * @param non-empty-string $needle
 * @param non-empty-string $start
 * @param non-empty-string $end
 */
function str_contains_between(string $haystack, string $needle, string $start, string $end): bool
{
    $startLen  = \strlen($start);
    $needleLen = \strlen($needle);
    $offset    = 0;

    while (($sPos = \strpos($haystack, $start, $offset)) !== false) {
        $afterStart = $sPos + $startLen;
        $ePos       = \strpos($haystack, $end, $afterStart);

        if ($ePos === false) {
            break;
        }

        $nPos = \strpos($haystack, $needle, $afterStart);

        if ($nPos !== false && $nPos + $needleLen <= $ePos) {
            return true;
        }

        $offset = $sPos + 1;
    }

    return false;
}
