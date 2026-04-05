--TEST--
The right events are emitted in the right order for a test that uses an empty data provider with skipWhenEmpty
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/EmptyDataProviderSkipWhenEmptyTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\Event\EmptyDataProviderSkipWhenEmptyTest::providerMethod for test method PHPUnit\TestFixture\Event\EmptyDataProviderSkipWhenEmptyTest::testCase)
Data Provider Method Finished for PHPUnit\TestFixture\Event\EmptyDataProviderSkipWhenEmptyTest::testCase:
- PHPUnit\TestFixture\Event\EmptyDataProviderSkipWhenEmptyTest::providerMethod
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\EmptyDataProviderSkipWhenEmptyTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\EmptyDataProviderSkipWhenEmptyTest::testCase)
Test Skipped (PHPUnit\TestFixture\Event\EmptyDataProviderSkipWhenEmptyTest::testCase)
The data provider for this test provided no data, which is explicitly permitted
Test Suite Finished (PHPUnit\TestFixture\Event\EmptyDataProviderSkipWhenEmptyTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
