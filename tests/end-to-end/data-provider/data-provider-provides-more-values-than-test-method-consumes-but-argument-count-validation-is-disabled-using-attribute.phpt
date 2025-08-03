--TEST--
phpunit ../../_files/DataProviderTooManyArgumentsTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../../_files/DataProviderTooManyArgumentsNoValidationTest.php';

require_once __DIR__ . '/../../bootstrap.php';
(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s

......                                                              6 / 6 (100%)

Time: %s, Memory: %s

OK (6 tests, 6 assertions)
