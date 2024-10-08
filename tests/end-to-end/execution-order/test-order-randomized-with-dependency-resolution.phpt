--TEST--
phpunit --order-by=depends,random ../_files/MultiDependencyTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--resolve-dependencies';     // keep coverage for legacy CLI option
$_SERVER['argv'][] = '--order-by=depends,random';
$_SERVER['argv'][] = __DIR__ . '/../../_files/MultiDependencyTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Random Seed:   %d

.....                                                               5 / 5 (100%)

Time: %s, Memory: %s

OK (5 tests, 6 assertions)
