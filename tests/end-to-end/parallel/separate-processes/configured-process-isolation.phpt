--TEST--
phpunit --parallel=2 --process-isolation runs every test in the main process without reporting a test runner notice, because the process isolation was asked for
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--process-isolation';
$_SERVER['argv'][] = '--display-phpunit-notices';
$_SERVER['argv'][] = '--testdox';
$_SERVER['argv'][] = __DIR__ . '/_files/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

..                                                                  2 / 2 (100%)

Time: %s, Memory: %s

Class Level Separate Process (PHPUnit\TestFixture\ParallelSeparateProcesses\ClassLevelSeparateProcess)
 ✔ Class level

Method Level Separate Process (PHPUnit\TestFixture\ParallelSeparateProcesses\MethodLevelSeparateProcess)
 ✔ Method level

OK (2 tests, 2 assertions)
