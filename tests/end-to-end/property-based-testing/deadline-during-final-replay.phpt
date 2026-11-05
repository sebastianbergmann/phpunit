--TEST--
A property whose final replay of the counterexample is aborted because the deadline of the test run has passed is reported with that error and with the counterexample
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) echo 'skip: Extension pcntl is required';
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--timeout';
$_SERVER['argv'][] = '1';
$_SERVER['argv'][] = __DIR__ . '/_files/SlowFinalReplayTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime: %s
Random Seed:   1234

E                                                                   1 / 1 (100%)

The time limit of 1 second for the test run was exceeded.

Time: %s, Memory: %s

There was 1 error:

1) PHPUnit\TestFixture\PropertyBasedTesting\SlowFinalReplayTest::testTakesLongerToReplayTheCounterexampleThanTheTimeLimitOfTheTestRun
PHPUnit\Runner\TimeLimit\TimeLimitExceededException: This test was aborted because the time limit of 1 second for the test run was exceeded

Property falsified after %s

    $value = %i;

Shrinking was stopped after %s because the test run was aborted, a simpler counterexample may exist.

%sSlowFinalReplayTest.php:%d

ERRORS!
Tests: 1, Assertions: %d, Errors: 1.
