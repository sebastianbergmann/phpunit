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

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

OK (3 tests, 5 assertions)
