--TEST--
Test Runner exits with shell exit code indicating failure when all tests are successful but at least one test suite was skipped
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--fail-on-skipped';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/SkippedBeforeClassTest.php';

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
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\SkippedBeforeClassTest, 1 test)
Before First Test Method Called (PHPUnit\TestFixture\TestRunnerStopping\SkippedBeforeClassTest::setUpBeforeClass)
Before First Test Method Finished:
- PHPUnit\TestFixture\TestRunnerStopping\SkippedBeforeClassTest::setUpBeforeClass
Test Suite Skipped (PHPUnit\TestFixture\TestRunnerStopping\SkippedBeforeClassTest, message)
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\SkippedBeforeClassTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
