--TEST--
phpunit --process-isolation --filter testFalse@false.* ../../_files/DataProviderFilterTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--process-isolation';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testFalse@false.*';
$_SERVER['argv'][] = __DIR__ . '/../_files/DataProviderFilterTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

OK (2 tests, 2 assertions)
