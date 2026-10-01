--TEST--
phpunit --parallel=2 boots a fresh worker process in place of one that died while running a unit that was not retried, so that the units that come after it run
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

.E.E..                                                              6 / 6 (100%)

Time: %s, Memory: %s

There were 2 errors:

1) PHPUnit\TestFixture\ParallelCrashRestart\ACrashingTest::testThatKillsTheWorkerProcess
The worker process running PHPUnit\TestFixture\ParallelCrashRestart\ACrashingTest ended unexpectedly

Fatal error: Premature end of PHP process when running PHPUnit\TestFixture\ParallelCrashRestart\ACrashingTest::testThatKillsTheWorkerProcess.

2) PHPUnit\TestFixture\ParallelCrashRestart\BCrashingTest::testThatKillsTheWorkerProcess
The worker process running PHPUnit\TestFixture\ParallelCrashRestart\BCrashingTest ended unexpectedly

Fatal error: Premature end of PHP process when running PHPUnit\TestFixture\ParallelCrashRestart\BCrashingTest::testThatKillsTheWorkerProcess.

ERRORS!
Tests: 6, Assertions: 4, Errors: 2.
