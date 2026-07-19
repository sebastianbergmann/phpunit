--TEST--
phpunit --parallel=2 reports every test of a crashed unit whose result never arrived as errored
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = __DIR__ . '/_files/CrashingTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/SteadyTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

.EE.                                                                4 / 4 (100%)

Time: %s, Memory: %s

There were 2 errors:

1) PHPUnit\TestFixture\ParallelCrashReport\CrashingTest::testThatKillsTheWorkerProcess
The worker process running PHPUnit\TestFixture\ParallelCrashReport\CrashingTest ended unexpectedly

2) PHPUnit\TestFixture\ParallelCrashReport\CrashingTest::testThatNeverRuns
The worker process running PHPUnit\TestFixture\ParallelCrashReport\CrashingTest ended unexpectedly

ERRORS!
Tests: 4, Assertions: 2, Errors: 2.
