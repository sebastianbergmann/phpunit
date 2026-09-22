--TEST--phpunit --parallel=2 runs tests requiring process isolation in the main process instead of a worker, and reports each such class with a test runner notice
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
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

There were 2 PHPUnit test runner notices:

1) The tests of class PHPUnit\TestFixture\ParallelSeparateProcesses\ClassLevelSeparateProcessTest are run in the main process instead of a parallel worker because its tests require process isolation, which a shared worker process cannot provide

2) The tests of class PHPUnit\TestFixture\ParallelSeparateProcesses\MethodLevelSeparateProcessTest are run in the main process instead of a parallel worker because its tests require process isolation, which a shared worker process cannot provide

OK, but there were issues!
Tests: 2, Assertions: 2, PHPUnit Notices: 2.
