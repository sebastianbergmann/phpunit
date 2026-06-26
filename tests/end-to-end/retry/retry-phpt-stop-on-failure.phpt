--TEST--
--stop-on-failure aborts the test run after a retried PHPT test exhausts all attempts
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--stop-on-failure';
$_SERVER['argv'][] = '--retry';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = realpath(__DIR__ . '/_files/RetryStopOnFailureFirstPhpt.phpt');
$_SERVER['argv'][] = realpath(__DIR__ . '/_files/RetryStopOnFailureSecondPhpt.phpt');

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
Test Suite Started (CLI Arguments, 2 tests)
Test Suite for Retried PHPT Started (%sRetryStopOnFailureFirstPhpt.phpt, up to 2 attempts)
Test Attempt Failed (%sRetryStopOnFailureFirstPhpt.phpt)
Failed asserting that two strings are equal.
Test Preparation Started (%sRetryStopOnFailureFirstPhpt.phpt (attempt 2 of 2))
Test Prepared (%sRetryStopOnFailureFirstPhpt.phpt (attempt 2 of 2))
Child Process Started
Child Process Finished
Test Failed (%sRetryStopOnFailureFirstPhpt.phpt (attempt 2 of 2))
Failed asserting that two strings are equal.
Test Finished (%sRetryStopOnFailureFirstPhpt.phpt (attempt 2 of 2))
Test Suite for Retried PHPT Finished (%sRetryStopOnFailureFirstPhpt.phpt, up to 2 attempts)
Test Runner Execution Aborted
Test Suite Finished (CLI Arguments, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
