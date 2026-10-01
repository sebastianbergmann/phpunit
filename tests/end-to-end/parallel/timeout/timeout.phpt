--TEST--
phpunit --parallel=2 --timeout 1 aborts the test that a worker is running when the time limit for the test run is exceeded, and ends the test run
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--timeout';
$_SERVER['argv'][] = '1';
$_SERVER['argv'][] = __DIR__ . '/_files/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers

.E

The time limit of 1 second for the test run was exceeded.

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\ParallelTimeout\ASlowTest::testSlow
PHPUnit\Runner\TimeLimit\TimeLimitExceededException: This test was aborted because the time limit of 1 second for the test run was exceeded

ERRORS!
Tests: 2, Assertions: 1, Errors: 1.
