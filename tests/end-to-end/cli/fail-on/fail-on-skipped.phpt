--TEST--
Test Runner exits with shell exit code indicating failure when all tests are successful but at least one test was skipped
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--fail-on-skipped';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/SkippedTest.php';

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
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\SkippedTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\SkippedTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\SkippedTest::testOne)
Test Skipped (PHPUnit\TestFixture\TestRunnerStopping\SkippedTest::testOne)
message
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\SkippedTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\SkippedTest::testTwo)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\SkippedTest::testTwo)
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\SkippedTest::testTwo)
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\SkippedTest::testTwo)
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\SkippedTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
