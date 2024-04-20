#!/usr/bin/env php
<?php declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';

if (!isset($argv[1]) || !is_file($argv[1])) {
    exit(1);
}

use Seld\PharUtils\Timestamps;

if (is_string(getenv('SOURCE_DATE_EPOCH'))) {
    $epoch = (int) getenv('SOURCE_DATE_EPOCH');

    printf(
        'Setting timestamp of files in PHAR to %d (based on environment variable SOURCE_DATE_EPOCH)' . PHP_EOL,
        $epoch
    );
} else {
    $tag = @shell_exec('git describe --abbrev=0 2>&1');

    if (is_string($tag) && strpos($tag, 'fatal') === false) {
        $tmp = @shell_exec('git log -1 --format=%at ' . trim($tag) . ' 2>&1');

        if (is_string($tag) && is_numeric(trim($tmp))) {
            $epoch = (int) trim($tmp);

            printf(
                'Setting timestamp of files in PHAR to %d (based on when tag %s was created)' . PHP_EOL,
                $epoch,
                trim($tag)
            );
        }

        unset($tmp);
    }

    unset($tag);
}

if (!isset($epoch)) {
    $epoch = time();

    printf(
        'Setting timestamp of files in PHAR to %d (based on current time)' . PHP_EOL,
        $epoch
    );
}

$timestamp = new DateTime;
$timestamp->setTimestamp($epoch);

$util = new Timestamps($argv[1]);
$util->updateTimestamps($timestamp);
$util->save($argv[1], Phar::SHA512);
