--TEST--
Test Runner exits with shell exit code indicating failure when all tests are successful but at least one test triggered a PHPUnit notice
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--fail-on-phpunit-notice';
$_SERVER['argv'][] = __DIR__ . '/../../_files/stop-on-fail-on/PhpunitNoticeTest.php';

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
Test Suite Started (PHPUnit\TestFixture\TestRunnerStopping\PhpunitNoticeTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\PhpunitNoticeTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\PhpunitNoticeTest::testOne)
Test Triggered PHPUnit Notice (PHPUnit\TestFixture\TestRunnerStopping\PhpunitNoticeTest::testOne)
message
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\PhpunitNoticeTest::testOne)
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\PhpunitNoticeTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\TestRunnerStopping\PhpunitNoticeTest::testTwo)
Test Prepared (PHPUnit\TestFixture\TestRunnerStopping\PhpunitNoticeTest::testTwo)
Test Passed (PHPUnit\TestFixture\TestRunnerStopping\PhpunitNoticeTest::testTwo)
Test Finished (PHPUnit\TestFixture\TestRunnerStopping\PhpunitNoticeTest::testTwo)
Test Suite Finished (PHPUnit\TestFixture\TestRunnerStopping\PhpunitNoticeTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
