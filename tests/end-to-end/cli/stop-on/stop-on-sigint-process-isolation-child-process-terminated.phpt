--TEST--
Stopping test execution on SIGINT that also terminates the child process of a test that is run in a separate process reports the test as skipped
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) {
    echo 'skip: Extension pcntl is required';
} elseif (!extension_loaded('posix')) {
    echo 'skip: Extension posix is required';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/InterruptTerminatingChildProcessTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\InterruptTerminatingChildProcessTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\InterruptTerminatingChildProcessTest::testOne)
Child Process Started (test requiring process isolation)
Child Process Finished (test requiring process isolation)
Test Skipped (PHPUnit\TestFixture\TestRunnerStopping\InterruptTerminatingChildProcessTest::testOne)
Test was run in child process and test execution was interrupted by a signal
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\InterruptTerminatingChildProcessTest::testOne)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\InterruptTerminatingChildProcessTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
Test execution was interrupted by a signal.
PHPUnit Finished (Shell Exit Code: 0)
