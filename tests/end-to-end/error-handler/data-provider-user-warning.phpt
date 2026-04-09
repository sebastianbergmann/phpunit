--TEST--
E_USER_WARNING triggered in data provider method emits Test Triggered Warning event
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DataProviderUserWarningTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\ErrorHandler\DataProviderUserWarningTest::provider for test method PHPUnit\TestFixture\ErrorHandler\DataProviderUserWarningTest::testOne)
Data Provider Method Finished for PHPUnit\TestFixture\ErrorHandler\DataProviderUserWarningTest::testOne:
- PHPUnit\TestFixture\ErrorHandler\DataProviderUserWarningTest::provider
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\DataProviderUserWarningTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\DataProviderUserWarningTest::testOne, 1 test)
Test Triggered Warning (PHPUnit\TestFixture\ErrorHandler\DataProviderUserWarningTest::testOne#0) in %sDataProviderUserWarningTest.php:%d
warning from data provider
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\DataProviderUserWarningTest::testOne#0)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\DataProviderUserWarningTest::testOne#0)
Test Passed (PHPUnit\TestFixture\ErrorHandler\DataProviderUserWarningTest::testOne#0)
Test Finished (PHPUnit\TestFixture\ErrorHandler\DataProviderUserWarningTest::testOne#0)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\DataProviderUserWarningTest::testOne, 1 test)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\DataProviderUserWarningTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
