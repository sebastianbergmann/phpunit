--TEST--
E_USER_DEPRECATED triggered in data provider method emits Test Triggered Deprecation event
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DataProviderUserDeprecationTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\ErrorHandler\DataProviderUserDeprecationTest::provider for test method PHPUnit\TestFixture\ErrorHandler\DataProviderUserDeprecationTest::testOne)
Data Provider Method Finished for PHPUnit\TestFixture\ErrorHandler\DataProviderUserDeprecationTest::testOne:
- PHPUnit\TestFixture\ErrorHandler\DataProviderUserDeprecationTest::provider
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\DataProviderUserDeprecationTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\DataProviderUserDeprecationTest::testOne, 1 test)
Test Triggered Deprecation (PHPUnit\TestFixture\ErrorHandler\DataProviderUserDeprecationTest::testOne#0, unknown if issue was triggered in first-party code or third-party code) in %sDataProviderUserDeprecationTest.php:%d
deprecation from data provider
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\DataProviderUserDeprecationTest::testOne#0)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\DataProviderUserDeprecationTest::testOne#0)
Test Passed (PHPUnit\TestFixture\ErrorHandler\DataProviderUserDeprecationTest::testOne#0)
Test Finished (PHPUnit\TestFixture\ErrorHandler\DataProviderUserDeprecationTest::testOne#0)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\DataProviderUserDeprecationTest::testOne, 1 test)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\DataProviderUserDeprecationTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
