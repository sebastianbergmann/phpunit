--TEST--
phpunit --repeat 2 ../../_files/repeat/single-test-class-that-uses-data-provider/tests/DataProviderTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = __DIR__ . '/../../_files/repeat/single-test-class-that-uses-data-provider/tests/DataProviderTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\Repeat\DataProviderTest::provider for test method PHPUnit\TestFixture\Repeat\DataProviderTest::testOne)
Data Provider Method Finished for PHPUnit\TestFixture\Repeat\DataProviderTest::testOne:
- PHPUnit\TestFixture\Repeat\DataProviderTest::provider
Data Provider Method Called (PHPUnit\TestFixture\Repeat\DataProviderTest::provider for test method PHPUnit\TestFixture\Repeat\DataProviderTest::testOne)
Data Provider Method Finished for PHPUnit\TestFixture\Repeat\DataProviderTest::testOne:
- PHPUnit\TestFixture\Repeat\DataProviderTest::provider
Test Suite Loaded (4 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (4 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\DataProviderTest, 4 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne, 4 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne#0)
Test Prepared (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne#0)
Test Passed (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne#0)
Test Finished (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne#0)
Test Preparation Started (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne#1)
Test Prepared (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne#1)
Test Passed (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne#1)
Test Finished (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne#1)
Test Suite Finished (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne, 2 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne#0)
Test Prepared (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne#0)
Test Passed (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne#0)
Test Finished (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne#0)
Test Preparation Started (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne#1)
Test Prepared (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne#1)
Test Passed (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne#1)
Test Finished (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne#1)
Test Suite Finished (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne, 2 tests)
Test Suite Finished (PHPUnit\TestFixture\Repeat\DataProviderTest::testOne, 4 tests)
Test Suite Finished (PHPUnit\TestFixture\Repeat\DataProviderTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
