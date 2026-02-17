--TEST--
Suppressed E_USER_NOTICE using @ operator emits event with suppressed using operator
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/SuppressedPhpNoticeTest.php';

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
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\SuppressedPhpNoticeTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\SuppressedPhpNoticeTest::testSuppressedNotice)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\SuppressedPhpNoticeTest::testSuppressedNotice)
Test Triggered Notice (PHPUnit\TestFixture\ErrorHandler\SuppressedPhpNoticeTest::testSuppressedNotice, suppressed using operator) in %s:%d
suppressed notice
Test Passed (PHPUnit\TestFixture\ErrorHandler\SuppressedPhpNoticeTest::testSuppressedNotice)
Test Finished (PHPUnit\TestFixture\ErrorHandler\SuppressedPhpNoticeTest::testSuppressedNotice)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\SuppressedPhpNoticeTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
