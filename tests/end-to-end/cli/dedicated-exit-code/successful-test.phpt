--TEST--
Test Runner exits with shell exit code indicating success when dedicated shell exit code is requested and no test failed or errored
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--dedicated-exit-code-for-failed-tests';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/SuccessTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\SuccessTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\SuccessTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\SuccessTest::testOne)
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\SuccessTest::testOne)
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\SuccessTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\SuccessTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
