--TEST--
phpunit --columns=1 ../../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--columns=1';
$_SERVER['argv'][] = __DIR__ . '/../../../_files/BankAccountTest.php';

require_once __DIR__ . '/../../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

... 3 / 3 (100%)


Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) Less than 16 columns requested, number of columns set to 16

OK, but there were issues!
Tests: 3, Assertions: 3, PHPUnit Warnings: 1.
