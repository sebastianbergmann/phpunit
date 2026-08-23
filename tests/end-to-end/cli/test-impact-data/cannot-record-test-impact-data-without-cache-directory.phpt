--TEST--
Test impact data cannot be recorded when no cache directory is configured
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--record-test-impact-data';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--colors=never';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/phpunit-without-cache-directory.xml';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Configuration: %s

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Cannot record test impact data because no cache directory is configured

OK, but there were issues!
Tests: 4, Assertions: 3, PHPUnit Warnings: 1, Skipped: 1.
