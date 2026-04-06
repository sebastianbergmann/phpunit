--TEST--
Stopping test execution after second skipped test works
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--stop-on-skipped=2';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/MultipleSkippedTest.php';

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
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleSkippedTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleSkippedTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\MultipleSkippedTest::testOne)
Test Skipped (PHPUnit\TestFixture\TestRunnerStopping\MultipleSkippedTest::testOne)
message
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleSkippedTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleSkippedTest::testTwo)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\MultipleSkippedTest::testTwo)
Test Skipped (PHPUnit\TestFixture\TestRunnerStopping\MultipleSkippedTest::testTwo)
message
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleSkippedTest::testTwo)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleSkippedTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
