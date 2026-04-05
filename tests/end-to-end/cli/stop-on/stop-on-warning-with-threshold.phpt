--TEST--
Stopping test execution after second warning works
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--stop-on-warning=2';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/MultipleWarningTest.php';

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
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleWarningTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleWarningTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\MultipleWarningTest::testOne)
Test Triggered Warning (PHPUnit\TestFixture\TestRunnerStopping\MultipleWarningTest::testOne) in %sMultipleWarningTest.php:%d
message
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\MultipleWarningTest::testOne)
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleWarningTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleWarningTest::testTwo)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\MultipleWarningTest::testTwo)
Test Triggered Warning (PHPUnit\TestFixture\TestRunnerStopping\MultipleWarningTest::testTwo) in %sMultipleWarningTest.php:%d
message
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\MultipleWarningTest::testTwo)
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleWarningTest::testTwo)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleWarningTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
