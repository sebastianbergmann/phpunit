--TEST--
phpunit --without-class-view --without-file-view ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--without-class-view';
$_SERVER['argv'][] = '--without-file-view';
$_SERVER['argv'][] = __DIR__ . '/../../_files/BankAccountTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) The class view and the file view of the code coverage report in HTML format cannot both be disabled, rendering both

OK, but there were issues!
Tests: 3, Assertions: 3, PHPUnit Warnings: 1.
