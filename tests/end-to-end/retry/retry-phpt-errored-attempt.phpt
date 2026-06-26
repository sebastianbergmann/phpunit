--TEST--
An errored attempt of a retried PHPT test is tolerated and retried
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--retry';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = realpath(__DIR__ . '/_files/RetryErroringPhpt.phpt');

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
Test Suite Started (%sRetryErroringPhpt.phpt, 1 test)
Test Suite for Retried PHPT Started (%sRetryErroringPhpt.phpt, up to 3 attempts)
Test Attempt Errored (%sRetryErroringPhpt.phpt)
Test Attempt Errored (%sRetryErroringPhpt.phpt (attempt 2 of 3))
Test Preparation Started (%sRetryErroringPhpt.phpt (attempt 3 of 3))
Test Prepared (%sRetryErroringPhpt.phpt (attempt 3 of 3))
Test Errored (%sRetryErroringPhpt.phpt (attempt 3 of 3))
Test Finished (%sRetryErroringPhpt.phpt (attempt 3 of 3))
Test Suite for Retried PHPT Finished (%sRetryErroringPhpt.phpt, up to 3 attempts)
Test Suite Finished (%sRetryErroringPhpt.phpt, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
