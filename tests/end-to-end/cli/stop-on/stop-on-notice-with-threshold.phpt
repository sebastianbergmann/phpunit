--TEST--
Stopping test execution after second notice works
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--stop-on-notice=2';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/MultipleNoticeTest.php';

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
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleNoticeTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleNoticeTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\MultipleNoticeTest::testOne)
Test Triggered Notice (PHPUnit\TestFixture\TestRunnerStopping\MultipleNoticeTest::testOne) in %sMultipleNoticeTest.php:%d
message
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\MultipleNoticeTest::testOne)
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleNoticeTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\MultipleNoticeTest::testTwo)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\MultipleNoticeTest::testTwo)
Test Triggered Notice (PHPUnit\TestFixture\TestRunnerStopping\MultipleNoticeTest::testTwo) in %sMultipleNoticeTest.php:%d
message
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\MultipleNoticeTest::testTwo)
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleNoticeTest::testTwo)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\MultipleNoticeTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
