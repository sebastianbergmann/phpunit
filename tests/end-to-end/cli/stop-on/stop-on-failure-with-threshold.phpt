--TEST--
Stopping test execution after second failure works
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--stop-on-failure=2';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/MultipleFailureTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (4 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (4 tests)
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleFailureTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleFailureTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\MultipleFailureTest::testOne)
Test Failed (PHPUnit\TestFixture\TestRunnerStopping\MultipleFailureTest::testOne)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleFailureTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleFailureTest::testTwo)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\MultipleFailureTest::testTwo)
Test Failed (PHPUnit\TestFixture\TestRunnerStopping\MultipleFailureTest::testTwo)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleFailureTest::testTwo)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleFailureTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
