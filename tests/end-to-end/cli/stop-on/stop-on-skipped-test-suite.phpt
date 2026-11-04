--TEST--
Stopping test execution after first skipped test suite works
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--stop-on-skipped';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/SkippedBeforeClassTest.php';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/SuccessTest.php';

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
Test Suite Started (CLI Arguments, 2 tests)
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\SkippedBeforeClassTest, 1 test)
Before First Test Method Called (PHPUnit\TestFixture\TestRunnerStopping\SkippedBeforeClassTest::setUpBeforeClass)
Before First Test Method Finished:
- PHPUnit\TestFixture\TestRunnerStopping\SkippedBeforeClassTest::setUpBeforeClass
Test Suite Skipped (PHPUnit\TestFixture\TestRunnerStopping\SkippedBeforeClassTest, message)
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\SkippedBeforeClassTest, 1 test)
Test Runner Execution Aborted
Test Suite Finished (CLI Arguments, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
