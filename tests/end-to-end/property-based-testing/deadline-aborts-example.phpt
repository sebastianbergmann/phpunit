--TEST--
An example that is aborted because the deadline of the test run has passed does not falsify the property, which is reported with that error and without shrinking
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) echo 'skip: Extension pcntl is required';
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--timeout';
$_SERVER['argv'][] = '1';
$_SERVER['argv'][] = __DIR__ . '/_files/SlowExampleTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\PropertyBasedTesting\SlowExampleTest, 2 tests)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\SlowExampleTest::testTakesLongerThanTheTimeLimitOfTheTestRun, 10 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\SlowExampleTest::testTakesLongerThanTheTimeLimitOfTheTestRun)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\SlowExampleTest::testTakesLongerThanTheTimeLimitOfTheTestRun)
Test Errored (PHPUnit\TestFixture\PropertyBasedTesting\SlowExampleTest::testTakesLongerThanTheTimeLimitOfTheTestRun)
This test was aborted because the time limit of 1 second for the test run was exceeded
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\SlowExampleTest::testTakesLongerThanTheTimeLimitOfTheTestRun, seed: %d, examples: 0, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\SlowExampleTest::testTakesLongerThanTheTimeLimitOfTheTestRun)
Test Runner Time Limit Exceeded (1 second)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\SlowExampleTest::testTakesLongerThanTheTimeLimitOfTheTestRun, 10 examples)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\PropertyBasedTesting\SlowExampleTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
The time limit of 1 second for the test run was exceeded.
PHPUnit Finished (Shell Exit Code: 124)
