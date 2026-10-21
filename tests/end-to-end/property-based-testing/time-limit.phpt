--TEST--
An example that exceeds the time limit falsifies the property, which is shrunk with the separate budget for timeouts and reported as an error
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
$_SERVER['argv'][] = __DIR__ . '/_files/TimeLimitTest.php';

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
Test Suite Started (PHPUnit\TestFixture\PropertyBasedTesting\TimeLimitTest, 1 test)
Test Suite for Property Started (PHPUnit\TestFixture\PropertyBasedTesting\TimeLimitTest::testDoesNotTerminateForValuesGreaterThanFifty, 50 examples)
Test Preparation Started (PHPUnit\TestFixture\PropertyBasedTesting\TimeLimitTest::testDoesNotTerminateForValuesGreaterThanFifty)
Test Prepared (PHPUnit\TestFixture\PropertyBasedTesting\TimeLimitTest::testDoesNotTerminateForValuesGreaterThanFifty)
Test Property Falsified (PHPUnit\TestFixture\PropertyBasedTesting\TimeLimitTest::testDoesNotTerminateForValuesGreaterThanFifty, seed: %d, examples: %d, shrinking steps: %d, shrinking attempts: %d, budget exhausted)
$value = 51;
Test Errored (PHPUnit\TestFixture\PropertyBasedTesting\TimeLimitTest::testDoesNotTerminateForValuesGreaterThanFifty)
Execution aborted after 1 second
Test Property Checked (PHPUnit\TestFixture\PropertyBasedTesting\TimeLimitTest::testDoesNotTerminateForValuesGreaterThanFifty, seed: %d, examples: %d, rejected: 0)
Test Finished (PHPUnit\TestFixture\PropertyBasedTesting\TimeLimitTest::testDoesNotTerminateForValuesGreaterThanFifty)
Test Suite for Property Finished (PHPUnit\TestFixture\PropertyBasedTesting\TimeLimitTest::testDoesNotTerminateForValuesGreaterThanFifty, 50 examples)
Test Suite Finished (PHPUnit\TestFixture\PropertyBasedTesting\TimeLimitTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
