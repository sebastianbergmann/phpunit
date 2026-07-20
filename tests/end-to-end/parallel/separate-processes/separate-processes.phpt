--TEST--
phpunit --parallel=2 runs tests requiring process isolation in the main process instead of a worker
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = __DIR__ . '/_files/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

Class Level Separate Process (PHPUnit\TestFixture\ParallelSeparateProcesses\ClassLevelSeparateProcess)
 ✔ Class level

Method Level Separate Process (PHPUnit\TestFixture\ParallelSeparateProcesses\MethodLevelSeparateProcess)
 ✔ Method level

OK (2 tests, 2 assertions)
