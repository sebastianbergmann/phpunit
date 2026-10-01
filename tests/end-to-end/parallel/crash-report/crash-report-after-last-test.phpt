--TEST--
phpunit --parallel=2 reports a worker process that ended unexpectedly after all tests of its unit had finished as a test runner warning, along with what the worker process wrote
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/CrashingAfterLastTestTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/SteadyTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

There was 1 PHPUnit test runner warning:

1) The worker process running PHPUnit\TestFixture\ParallelCrashReport\CrashingAfterLastTestTest ended unexpectedly

output of the worker process before it ended

OK, but there were issues!
Tests: 3, Assertions: 3, PHPUnit Warnings: 1.
