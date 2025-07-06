--TEST--
phpunit --log-otr /invalid/path ../../event/_files/basic/SuccessTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--log-otr';
$_SERVER['argv'][] = '/invalid/path';
$_SERVER['argv'][] = __DIR__ . '/../../_files/basic/SuccessTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Cannot log test results in Open Test Reporting XML format to "/invalid/path": Unable to resolve file path

OK, but there were issues!
Tests: 1, Assertions: 1, PHPUnit Warnings: 1.
