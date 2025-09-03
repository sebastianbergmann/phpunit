--TEST--
phpunit ../../_files/DataProviderMethodHasTestAttributeTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../../_files/DataProviderMethodHasTestAttributeTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\DataProviderMethodHasTestAttributeTest::provider for test method PHPUnit\TestFixture\DataProviderMethodHasTestAttributeTest::testOne)
Test Runner Triggered Warning (Method PHPUnit\TestFixture\DataProviderMethodHasTestAttributeTest::provider() used by test method PHPUnit\TestFixture\DataProviderMethodHasTestAttributeTest::testOne() is also a test method)
Data Provider Method Finished for PHPUnit\TestFixture\DataProviderMethodHasTestAttributeTest::testOne:
- PHPUnit\TestFixture\DataProviderMethodHasTestAttributeTest::provider
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\DataProviderMethodHasTestAttributeTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\DataProviderMethodHasTestAttributeTest::provider)
Test Prepared (PHPUnit\TestFixture\DataProviderMethodHasTestAttributeTest::provider)
Test Passed (PHPUnit\TestFixture\DataProviderMethodHasTestAttributeTest::provider)
Test Considered Risky (PHPUnit\TestFixture\DataProviderMethodHasTestAttributeTest::provider)
This test did not perform any assertions
Test Finished (PHPUnit\TestFixture\DataProviderMethodHasTestAttributeTest::provider)
Test Suite Started (PHPUnit\TestFixture\DataProviderMethodHasTestAttributeTest::testOne, 1 test)
Test Preparation Started (PHPUnit\TestFixture\DataProviderMethodHasTestAttributeTest::testOne#0)
Test Prepared (PHPUnit\TestFixture\DataProviderMethodHasTestAttributeTest::testOne#0)
Test Passed (PHPUnit\TestFixture\DataProviderMethodHasTestAttributeTest::testOne#0)
Test Finished (PHPUnit\TestFixture\DataProviderMethodHasTestAttributeTest::testOne#0)
Test Suite Finished (PHPUnit\TestFixture\DataProviderMethodHasTestAttributeTest::testOne, 1 test)
Test Suite Finished (PHPUnit\TestFixture\DataProviderMethodHasTestAttributeTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
