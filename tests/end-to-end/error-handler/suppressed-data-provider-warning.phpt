--TEST--
An error suppressed using the @ operator in a data provider emits an event with suppressed using operator
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DataProviderSuppressedTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::provider for test method PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::testSuppressedWarningFromDataProvider)
Data Provider Method Finished for PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::testSuppressedWarningFromDataProvider:
- PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::provider
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest, 1 test)
Test Suite for Test Method with Data Provider Started (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::testSuppressedWarningFromDataProvider, 1 data set)
Test Triggered Warning (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::testSuppressedWarningFromDataProvider#0, suppressed using operator) in %s:%d
suppressed warning from data provider
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::testSuppressedWarningFromDataProvider#0)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::testSuppressedWarningFromDataProvider#0)
Test Passed (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::testSuppressedWarningFromDataProvider#0)
Test Finished (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::testSuppressedWarningFromDataProvider#0)
Test Suite for Test Method with Data Provider Finished (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest::testSuppressedWarningFromDataProvider, 1 data set)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\DataProviderSuppressedTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
