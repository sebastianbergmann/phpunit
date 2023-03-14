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
if (!isset($argv[1], $argv[2])) {
    exit(1);
}

\file_put_contents(
    __DIR__ . '/../tmp/phar/phpunit/Runner/Version.php',
    \str_replace(
        'private static $pharVersion = \'\';',
        'private static $pharVersion = \'' . $argv[1] . '\';',
        \file_get_contents(__DIR__ . '/../tmp/phar/phpunit/Runner/Version.php')
    ),
    \LOCK_EX
);

if ($argv[2] === 'release') {
    print $argv[1];
} else {
    print $argv[2];
}
