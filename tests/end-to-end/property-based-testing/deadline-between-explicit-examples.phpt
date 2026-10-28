--TEST--
No further explicit examples of a property are checked once the deadline of the test run has passed, no examples are generated, and the property is considered risky
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--timeout';
$_SERVER['argv'][] = '1';
$_SERVER['argv'][] = __DIR__ . '/_files/SlowExplicitExamplesTest.php';

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
Test Suite Started (PHPUnit\TestFixture\PropertyBasedTesting\SlowExplicitExamplesTest, 2 tests)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\SlowExplicitExamplesTest::testTakesLongerThanTheTimeLimitOfTheTestRun, 10 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\SlowExplicitExamplesTest::testTakesLongerThanTheTimeLimitOfTheTestRun)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\SlowExplicitExamplesTest::testTakesLongerThanTheTimeLimitOfTheTestRun)
Test Passed (PHPUnit\TestFixture\PropertyBasedTesting\SlowExplicitExamplesTest::testTakesLongerThanTheTimeLimitOfTheTestRun)
Test Considered Risky (PHPUnit\TestFixture\PropertyBasedTesting\SlowExplicitExamplesTest::testTakesLongerThanTheTimeLimitOfTheTestRun)
This property was checked for only 0 of 10 generated examples (and 1 explicit example) because the test run was aborted
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\SlowExplicitExamplesTest::testTakesLongerThanTheTimeLimitOfTheTestRun, seed: %d, examples: 0, explicit examples: 1, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\SlowExplicitExamplesTest::testTakesLongerThanTheTimeLimitOfTheTestRun)
Test Runner Time Limit Exceeded (1 second)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\SlowExplicitExamplesTest::testTakesLongerThanTheTimeLimitOfTheTestRun, 10 examples)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\PropertyBasedTesting\SlowExplicitExamplesTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
The time limit of 1 second for the test run was exceeded.
PHPUnit Finished (Shell Exit Code: 124)
