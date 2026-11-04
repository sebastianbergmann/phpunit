--TEST--
The tests of a skipped test suite are counted when stopping test execution after the second skipped test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--stop-on-skipped=2';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/SkippedBeforeClassTest.php';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/MultipleSkippedTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (5 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (5 tests)
Test Suite Started (CLI Arguments, 5 tests)
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\SkippedBeforeClassTest, 1 test)
Before First Test Method Called (PHPUnit\TestFixture\TestRunnerStopping\SkippedBeforeClassTest::setUpBeforeClass)
Test Suite Skipped (PHPUnit\TestFixture\TestRunnerStopping\SkippedBeforeClassTest, message)
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleSkippedTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleSkippedTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\MultipleSkippedTest::testOne)
Test Skipped (PHPUnit\TestFixture\TestRunnerStopping\MultipleSkippedTest::testOne)
message
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleSkippedTest::testOne)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleSkippedTest, 4 tests)
Test Suite Finished (CLI Arguments, 5 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
