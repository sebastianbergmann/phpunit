--TEST--
phpunit ../../_files/DataProviderTooManyArgumentsTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/DataProviderTooManyArgumentsTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

W......                                                             7 / 7 (100%)

Time: %s, Memory: %s

1 test triggered 1 PHPUnit warning:

1) PHPUnit\TestFixture\DataProviderTooManyArgumentsTest::testMethodHavingTwoParameters
Data set #2 has more arguments (3) than the test method accepts (2)

%s:%d

OK, but there were issues!
Tests: 7, Assertions: 7, PHPUnit Warnings: 1.
