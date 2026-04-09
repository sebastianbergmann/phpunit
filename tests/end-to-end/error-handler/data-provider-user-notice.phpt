--TEST--
E_USER_NOTICE triggered in data provider method emits Test Triggered Notice event
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DataProviderUserNoticeTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\ErrorHandler\DataProviderUserNoticeTest::provider for test method PHPUnit\TestFixture\ErrorHandler\DataProviderUserNoticeTest::testOne)
Data Provider Method Finished for PHPUnit\TestFixture\ErrorHandler\DataProviderUserNoticeTest::testOne:
- PHPUnit\TestFixture\ErrorHandler\DataProviderUserNoticeTest::provider
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\DataProviderUserNoticeTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\DataProviderUserNoticeTest::testOne, 1 test)
Test Triggered Notice (PHPUnit\TestFixture\ErrorHandler\DataProviderUserNoticeTest::testOne#0) in %sDataProviderUserNoticeTest.php:%d
notice from data provider
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\DataProviderUserNoticeTest::testOne#0)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\DataProviderUserNoticeTest::testOne#0)
Test Passed (PHPUnit\TestFixture\ErrorHandler\DataProviderUserNoticeTest::testOne#0)
Test Finished (PHPUnit\TestFixture\ErrorHandler\DataProviderUserNoticeTest::testOne#0)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\DataProviderUserNoticeTest::testOne, 1 test)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\DataProviderUserNoticeTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
