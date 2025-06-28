--TEST--
phpunit ../../_files/DataProviderDependencyVoidTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/DataProviderDependencyVoidTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

..EEE                                                               5 / 5 (100%)

Time: %s, Memory: %s

There were 3 errors:

1) PHPUnit\TestFixture\DataProviderDependencyVoidTest::testEquality with data set #1 (1, 1)
Error: Cannot use positional argument after named argument during unpacking

2) PHPUnit\TestFixture\DataProviderDependencyVoidTest::testEquality with data set #2 (2, 2)
Error: Cannot use positional argument after named argument during unpacking

3) PHPUnit\TestFixture\DataProviderDependencyVoidTest::testEquality with data set #3 (3, 3)
Error: Cannot use positional argument after named argument during unpacking

ERRORS!
Tests: 5, Assertions: 2, Errors: 3.
