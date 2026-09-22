--TEST--
The --timeout CLI option stops the test run after the test that exceeded the time limit when that test ran in a separate process
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--timeout';
$_SERVER['argv'][] = '1';
$_SERVER['argv'][] = __DIR__ . '/../../_files/timeout/SlowTestInSeparateProcessTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Timeout\SlowTestInSeparateProcessTest, 2 tests)
Child Process Started (test requiring process isolation)
Test Preparation Started (PHPUnit\TestFixture\Timeout\SlowTestInSeparateProcessTest::testOne)
Test Prepared (PHPUnit\TestFixture\Timeout\SlowTestInSeparateProcessTest::testOne)
Test Passed (PHPUnit\TestFixture\Timeout\SlowTestInSeparateProcessTest::testOne)
Test Finished (PHPUnit\TestFixture\Timeout\SlowTestInSeparateProcessTest::testOne)
Test Runner Time Limit Exceeded (1 second)
Child Process Finished (test requiring process isolation)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\Timeout\SlowTestInSeparateProcessTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
The time limit of 1 second for the test run was exceeded.
PHPUnit Finished (Shell Exit Code: 124)
