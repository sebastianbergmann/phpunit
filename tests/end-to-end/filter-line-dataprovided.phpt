--TEST--
phpunit ../../_files/DataProviderFilterTest.php:39
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = __DIR__ . '/../_files/DataProviderFilterTest.php:39';

require_once __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

....                                                                4 / 4 (100%)

Time: %s, Memory: %s

OK (4 tests, 4 assertions)
