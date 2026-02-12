--TEST--
E_NOTICE triggered by PHP runtime emits Test Triggered PHP Notice event
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/PhpNoticeTest.php';

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
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\PhpNoticeTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\PhpNoticeTest::testPhpNotice)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\PhpNoticeTest::testPhpNotice)
Test Triggered PHP Notice (PHPUnit\TestFixture\ErrorHandler\PhpNoticeTest::testPhpNotice) in %s:%d
Only variables should be assigned by reference
Test Passed (PHPUnit\TestFixture\ErrorHandler\PhpNoticeTest::testPhpNotice)
Test Finished (PHPUnit\TestFixture\ErrorHandler\PhpNoticeTest::testPhpNotice)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\PhpNoticeTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
