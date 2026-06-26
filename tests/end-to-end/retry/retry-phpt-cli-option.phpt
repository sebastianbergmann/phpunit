--TEST--
The --retry CLI option attempts a PHPT test up to N times, stopping at the first success
--FILE--
<?php declare(strict_types=1);
@unlink(sys_get_temp_dir() . '/phpunit-e2e-phpt-retry.marker');

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--retry';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = realpath(__DIR__ . '/_files/RetryFlakyPhpt.phpt');

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
Test Suite Started (%sRetryFlakyPhpt.phpt, 1 test)
Test Suite for Retried PHPT Started (%sRetryFlakyPhpt.phpt, up to 3 attempts)
Test Attempt Failed (%sRetryFlakyPhpt.phpt)
Failed asserting that two strings are equal.
Test Preparation Started (%sRetryFlakyPhpt.phpt (attempt 2 of 3))
Test Prepared (%sRetryFlakyPhpt.phpt (attempt 2 of 3))
Child Process Started
Child Process Finished
Test Passed (%sRetryFlakyPhpt.phpt (attempt 2 of 3))
Test Finished (%sRetryFlakyPhpt.phpt (attempt 2 of 3))
Test Suite for Retried PHPT Finished (%sRetryFlakyPhpt.phpt, up to 3 attempts)
Test Suite Finished (%sRetryFlakyPhpt.phpt, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
