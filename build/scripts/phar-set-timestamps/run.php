#!/usr/bin/env php
<?php declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/../source-date-epoch.php';

if (!isset($argv[1]) || !is_file($argv[1])) {
    exit(1);
}

use Seld\PharUtils\Timestamps;

$type = 'snapshot';

if (isset($argv[2])) {
    $type = $argv[2];
}

$epoch = sourceDateEpoch($type);

printf(
    'Setting timestamp of files in PHAR to %d (%s)' . PHP_EOL,
    $epoch['epoch'],
    $epoch['reason']
);

$timestamp = new DateTime;
$timestamp->setTimestamp($epoch['epoch']);

$util = new Timestamps($argv[1]);
$util->updateTimestamps($timestamp);
$util->save($argv[1], Phar::SHA512);
