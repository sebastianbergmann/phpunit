--TEST--
phpunit --repeat 0 ../../_files/basic/SuccessTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '0';
$_SERVER['argv'][] = __DIR__ . '/../../_files/basic/SuccessTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

.                                                                   1 / 1 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Option "--repeat 0" ignored because "0" is not a positive integer

WARNINGS!
Tests: 1, Assertions: 1, Warnings: 1.
