--TEST--
Stopping test execution after second defect (across types) works
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--stop-on-defect=2';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/MultipleDefectTest.php';

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
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleDefectTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleDefectTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\MultipleDefectTest::testOne)
Test Failed (PHPUnit\TestFixture\TestRunnerStopping\MultipleDefectTest::testOne)
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleDefectTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleDefectTest::testTwo)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\MultipleDefectTest::testTwo)
Test Errored (PHPUnit\TestFixture\TestRunnerStopping\MultipleDefectTest::testTwo)
message
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleDefectTest::testTwo)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleDefectTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
