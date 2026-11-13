--TEST--
Test Runner exits with dedicated shell exit code when it is requested and a test errored
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--dedicated-exit-code-for-failed-tests';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/ErrorTest.php';

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
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\ErrorTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\ErrorTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\ErrorTest::testOne)
Test Errored (PHPUnit\TestFixture\TestRunnerStopping\ErrorTest::testOne)
message
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\ErrorTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\ErrorTest::testTwo)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\ErrorTest::testTwo)
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\ErrorTest::testTwo)
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\ErrorTest::testTwo)
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\ErrorTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 3)
