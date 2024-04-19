#!/usr/bin/env php
<?php declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';

if (!isset($argv[1]) || !is_file($argv[1])) {
    exit(1);
}

use Seld\PharUtils\Timestamps;

if (is_string(getenv('SOURCE_DATE_EPOCH'))) {
    $epoch = (int) getenv('SOURCE_DATE_EPOCH');
} else {
    $epoch = (int) trim(shell_exec('git log -1 --format=%at ' . trim(shell_exec('git describe --abbrev=0'))));
}

$timestamp = new DateTime;
$timestamp->setTimestamp($epoch);

$util = new Timestamps($argv[1]);
$util->updateTimestamps($timestamp);
$util->save($argv[1], Phar::SHA512);
