--TEST--
phpunit --parallel=2 --timeout 1 aborts the property that a worker is checking when the time limit for the test run is exceeded as it aborts any other test that a worker is running: the property is reported as errored, not as risky because it was checked for only some of its examples, and without statistics
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--display-property-statistics';
$_SERVER['argv'][] = '--parallel=2';
$_SERVER['argv'][] = '--timeout';
$_SERVER['argv'][] = '1';
$_SERVER['argv'][] = __DIR__ . '/_files/timeout/';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s
Parallel:      2 workers
Random Seed:   1234

E                                                                   1 / 1 (100%)

The time limit of 1 second for the test run was exceeded.

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\ParallelProperty\SlowTest::testIsBeingCheckedWhenTheTimeLimitIsExceeded
PHPUnit\Runner\TimeLimit\TimeLimitExceededException: This test was aborted because the time limit of 1 second for the test run was exceeded

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
