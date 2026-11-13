--TEST--
Test Runner does not exit with dedicated shell exit code when it is requested and a test runner warning stopped the test run before the first test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--dedicated-exit-code-for-failed-tests';
$_SERVER['argv'][] = '--stop-on-defect';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = '--no-progress';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/FailureTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Triggered PHPUnit Warning (Option --no-progress cannot be used more than once)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\FailureTest, 2 tests)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\FailureTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
