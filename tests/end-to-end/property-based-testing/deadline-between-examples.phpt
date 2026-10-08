--TEST--
No further examples of a property are checked once the deadline of the test run has passed
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--timeout';
$_SERVER['argv'][] = '1';
$_SERVER['argv'][] = __DIR__ . '/_files/FastExamplesTest.php';

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
Test Suite Started (PHPUnit\TestFixture\PropertyBasedTesting\FastExamplesTest, 2 tests)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\FastExamplesTest::testHasMoreExamplesThanFitIntoTheTimeLimitOfTheTestRun, 100000 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\FastExamplesTest::testHasMoreExamplesThanFitIntoTheTimeLimitOfTheTestRun)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\FastExamplesTest::testHasMoreExamplesThanFitIntoTheTimeLimitOfTheTestRun)
Test Passed (PHPUnit\TestFixture\PropertyBasedTesting\FastExamplesTest::testHasMoreExamplesThanFitIntoTheTimeLimitOfTheTestRun)
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\FastExamplesTest::testHasMoreExamplesThanFitIntoTheTimeLimitOfTheTestRun, seed: %d, examples: %d, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\FastExamplesTest::testHasMoreExamplesThanFitIntoTheTimeLimitOfTheTestRun)
Test Runner Time Limit Exceeded (1 second)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\FastExamplesTest::testHasMoreExamplesThanFitIntoTheTimeLimitOfTheTestRun, 100000 examples)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\PropertyBasedTesting\FastExamplesTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
The time limit of 1 second for the test run was exceeded.
PHPUnit Finished (Shell Exit Code: 124)
