--TEST--
DataProvider: numeric string keys that PHP does not canonicalize remain distinct
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../_files/data-provider/NumericStringKeysTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::values for test method PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne)
Data Provider Method Finished for PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne:
- PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::values
Test Suite Loaded (4 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (4 tests)
Test Suite Started (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest, 4 tests)
Test Suite for Test Method with Data Provider Started (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne, 4 data sets)
Test Preparation Started (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne#1.5)
Test Prepared (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne#1.5)
Test Passed (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne#1.5)
Test Finished (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne#1.5)
Test Preparation Started (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne#1.9)
Test Prepared (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne#1.9)
Test Passed (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne#1.9)
Test Finished (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne#1.9)
Test Preparation Started (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne#0123)
Test Prepared (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne#0123)
Test Passed (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne#0123)
Test Finished (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne#0123)
Test Preparation Started (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne#0)
Test Prepared (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne#0)
Test Passed (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne#0)
Test Finished (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne#0)
Test Suite for Test Method with Data Provider Finished (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest::testOne, 4 data sets)
Test Suite Finished (PHPUnit\TestFixture\DataProvider\NumericStringKeysTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
