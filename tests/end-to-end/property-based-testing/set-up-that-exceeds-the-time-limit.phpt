--TEST--
An example of a property that exceeds the time limit before it is prepared, in setUp(), for instance, does not check the property, and exceeding the time limit is reported as the error of the property
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
$_SERVER['argv'][] = '--enforce-time-limit';
$_SERVER['argv'][] = __DIR__ . '/_files/SetUpThatExceedsTheTimeLimitTest.php';

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
Test Suite Started (PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest, 2 tests)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest::testGeneratedExample, 10 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest::testGeneratedExample)
Before Test Method Called (PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest::setUp)
Before Test Method Errored (PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest::setUp)
Execution aborted after 1 second
Before Test Method Finished:
- PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest::setUp
Test Errored (PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest::testGeneratedExample)
Execution aborted after 1 second
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest::testGeneratedExample, seed: %d, examples: 0, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest::testGeneratedExample)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest::testGeneratedExample, 10 examples)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest::testExplicitExample, 10 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest::testExplicitExample)
Before Test Method Called (PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest::setUp)
Before Test Method Errored (PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest::setUp)
Execution aborted after 1 second
Before Test Method Finished:
- PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest::setUp
Test Errored (PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest::testExplicitExample)
Execution aborted after 1 second
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest::testExplicitExample, seed: %d, examples: 0, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest::testExplicitExample)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest::testExplicitExample, 10 examples)
Test Suite Finished (PHPUnit\TestFixture\PropertyBasedTesting\SetUpThatExceedsTheTimeLimitTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
