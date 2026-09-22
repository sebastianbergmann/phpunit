--TEST--
The --timeout CLI option stops the test run after the test that exceeded the time limit when the pcntl extension is not available
--SKIPIF--
<?php declare(strict_types=1);
if (extension_loaded('pcntl')) echo 'skip: Extension pcntl must not be loaded';
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--timeout';
$_SERVER['argv'][] = '1';
$_SERVER['argv'][] = __DIR__ . '/../../_files/timeout/SlowTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Timeout\SlowTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Timeout\SlowTest::testOne)
Test Prepared (PHPUnit\TestFixture\Timeout\SlowTest::testOne)
Test Passed (PHPUnit\TestFixture\Timeout\SlowTest::testOne)
Test Finished (PHPUnit\TestFixture\Timeout\SlowTest::testOne)
Test Runner Time Limit Exceeded (1 second)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\Timeout\SlowTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
The time limit of 1 second for the test run was exceeded.
PHPUnit Finished (Shell Exit Code: 124)
