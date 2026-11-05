--TEST--
An explicit example that exceeds the time limit falsifies the property, which is reported as an error
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
$_SERVER['argv'][] = __DIR__ . '/_files/ExplicitExampleThatExceedsTheTimeLimitTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\PropertyBasedTesting\ExplicitExampleThatExceedsTheTimeLimitTest, 1 test)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\ExplicitExampleThatExceedsTheTimeLimitTest::testDoesNotTerminateForValuesGreaterThanFifty, 10 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\ExplicitExampleThatExceedsTheTimeLimitTest::testDoesNotTerminateForValuesGreaterThanFifty)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\ExplicitExampleThatExceedsTheTimeLimitTest::testDoesNotTerminateForValuesGreaterThanFifty)
Test Property Falsified (PHPUnit\TestFixture\PropertyBasedTesting\ExplicitExampleThatExceedsTheTimeLimitTest::testDoesNotTerminateForValuesGreaterThanFifty, explicit example #0)
$value = 51;
Test Errored (PHPUnit\TestFixture\PropertyBasedTesting\ExplicitExampleThatExceedsTheTimeLimitTest::testDoesNotTerminateForValuesGreaterThanFifty)
Execution aborted after 1 second
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\ExplicitExampleThatExceedsTheTimeLimitTest::testDoesNotTerminateForValuesGreaterThanFifty, seed: %d, examples: 0, explicit examples: 1, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\ExplicitExampleThatExceedsTheTimeLimitTest::testDoesNotTerminateForValuesGreaterThanFifty)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\ExplicitExampleThatExceedsTheTimeLimitTest::testDoesNotTerminateForValuesGreaterThanFifty, 10 examples)
Test Suite Finished (PHPUnit\TestFixture\PropertyBasedTesting\ExplicitExampleThatExceedsTheTimeLimitTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
