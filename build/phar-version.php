#!/usr/bin/env php
<?php
file_put_contents(
    __DIR__ . '/phar/phpunit/Runner/Version.php',
    str_replace(
        'private static $pharVersion;',
        'private static $pharVersion = "' . $argv[1] . '";',
        file_get_contents(__DIR__ . '/phar/phpunit/Runner/Version.php')
    )
);
