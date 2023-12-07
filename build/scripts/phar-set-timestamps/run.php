#!/usr/bin/env php
<?php declare(strict_types=1);
require __DIR__ . '/vendor/autoload.php';

if (!isset($argv[1]) || !is_file($argv[1])) {
    exit(1);
}

use Seld\PharUtils\Timestamps;

$util = new Timestamps($argv[1]);

if (is_string(getenv('SOURCE_DATE_EPOCH'))) {
    $timestamp = new DateTime;
    $timestamp->setTimestamp((int) getenv('SOURCE_DATE_EPOCH'));
} else {
    $timestamp = new DateTimeImmutable('now');
}

$util->updateTimestamps($timestamp);

$util->save($argv[1], Phar::SHA512);
