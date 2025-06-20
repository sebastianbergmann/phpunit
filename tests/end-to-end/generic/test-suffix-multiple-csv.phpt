--TEST--
phpunit --test-suffix .test.php,.my.php ../../_files/
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--test-suffix';
$_SERVER['argv'][] = '.test.php,.my.php';
$_SERVER['argv'][] = __DIR__ . '/../../_files/';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.....                                                               5 / 5 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Using comma-separated values with --test-suffix is deprecated and will no longer work in PHPUnit 12. You can use --test-suffix multiple times instead.

OK, but there were issues!
Tests: 5, Assertions: 5, PHPUnit Warnings: 1.
