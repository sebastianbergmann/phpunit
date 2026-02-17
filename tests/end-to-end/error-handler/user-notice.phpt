--TEST--
E_USER_NOTICE triggered by trigger_error() emits Test Triggered Notice event
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/UserNoticeTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\UserNoticeTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\UserNoticeTest::testUserNotice)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\UserNoticeTest::testUserNotice)
Test Triggered Notice (PHPUnit\TestFixture\ErrorHandler\UserNoticeTest::testUserNotice) in %s:%d
notice message
Test Passed (PHPUnit\TestFixture\ErrorHandler\UserNoticeTest::testUserNotice)
Test Finished (PHPUnit\TestFixture\ErrorHandler\UserNoticeTest::testUserNotice)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\UserNoticeTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
