--TEST--
Stopping test execution after first failure works
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--stop-on-failure';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/FailureTest.php';

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
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\FailureTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\FailureTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\FailureTest::testOne)
Test Failed (PHPUnit\TestFixture\TestRunnerStopping\FailureTest::testOne)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\FailureTest::testOne)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\FailureTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
