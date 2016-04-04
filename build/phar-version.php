#!/usr/bin/env php
<?php
if (!isset($argv[1]) || !isset($argv[2])) {
    exit(1);
}

file_put_contents(
    __DIR__ . '/phar/phpunit/Runner/Version.php',
    str_replace(
        'private static $pharVersion;',
        'private static $pharVersion = "' . $argv[1] . '";',
        file_get_contents(__DIR__ . '/phar/phpunit/Runner/Version.php')
    )
);

if ($argv[2] == 'release') {
    print $argv[1];
} else {
    print $argv[2];
}
