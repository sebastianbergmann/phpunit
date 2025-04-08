--TEST--
phpunit ../../_files/DataProviderDependencyResultTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/DataProviderDependencyResultTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

...E                                                                4 / 4 (100%)

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\DataProviderDependencyResultTest::testAdd with data set #2 (2, 0)
Error: Cannot use positional argument after named argument during unpacking

ERRORS!
Tests: 4, Assertions: 5, Errors: 1.
