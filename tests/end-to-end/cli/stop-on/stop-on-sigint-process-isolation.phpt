--TEST--
Stopping test execution on SIGINT while process isolation test is running does not report the test as failed
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) echo 'skip: Extension pcntl is required';
if (!extension_loaded('posix')) echo 'skip: Extension posix is required';
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/InterruptProcessIsolationTest.php';

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
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\InterruptProcessIsolationTest, 2 tests)
Child Process Started
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\InterruptProcessIsolationTest::testOne)
Child Process Finished
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\InterruptProcessIsolationTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
Test execution was interrupted by a signal.
PHPUnit Finished (Shell Exit Code: 0)
