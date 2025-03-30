--TEST--
The right events are emitted in the right order for a test that runs code which triggers a suppressed E_USER_NOTICE
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/SuppressedUserNoticeTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNotice)
Test Prepared (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNotice)
Test Triggered Notice (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNotice, suppressed using operator) in %s:%d
message
Test Passed (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNotice)
Test Finished (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNotice)
Test Preparation Started (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNoticeErrorGetLast)
Test Prepared (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNoticeErrorGetLast)
Test Triggered Notice (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNoticeErrorGetLast, suppressed using operator) in %s:%d
message
Test Passed (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNoticeErrorGetLast)
Test Finished (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNoticeErrorGetLast)
Test Suite Finished (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
