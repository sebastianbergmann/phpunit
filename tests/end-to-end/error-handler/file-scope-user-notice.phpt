--TEST--
E_USER_NOTICE triggered at file scope emits Test Runner Triggered Notice event
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/FileScopeUserNoticeTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Runner Triggered Notice () in %sFileScopeUserNoticeTest.php:%d
file scope user notice
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\FileScopeUserNoticeTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\FileScopeUserNoticeTest::testSuccess)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\FileScopeUserNoticeTest::testSuccess)
Test Passed (PHPUnit\TestFixture\ErrorHandler\FileScopeUserNoticeTest::testSuccess)
Test Finished (PHPUnit\TestFixture\ErrorHandler\FileScopeUserNoticeTest::testSuccess)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\FileScopeUserNoticeTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
