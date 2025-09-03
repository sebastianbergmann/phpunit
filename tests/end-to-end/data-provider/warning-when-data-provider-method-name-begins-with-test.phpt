--TEST--
phpunit ../../_files/DataProviderMethodNameStartsWithTestTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/../../_files/DataProviderMethodNameStartsWithTestTest.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\DataProviderMethodNameStartsWithTestTest::testProvider for test method PHPUnit\TestFixture\DataProviderMethodNameStartsWithTestTest::testOne)
Test Runner Triggered Warning (Method PHPUnit\TestFixture\DataProviderMethodNameStartsWithTestTest::testProvider() used by test method PHPUnit\TestFixture\DataProviderMethodNameStartsWithTestTest::testOne() is also a test method)
Data Provider Method Finished for PHPUnit\TestFixture\DataProviderMethodNameStartsWithTestTest::testOne:
- PHPUnit\TestFixture\DataProviderMethodNameStartsWithTestTest::testProvider
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\DataProviderMethodNameStartsWithTestTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\DataProviderMethodNameStartsWithTestTest::testProvider)
Test Prepared (PHPUnit\TestFixture\DataProviderMethodNameStartsWithTestTest::testProvider)
Test Passed (PHPUnit\TestFixture\DataProviderMethodNameStartsWithTestTest::testProvider)
Test Considered Risky (PHPUnit\TestFixture\DataProviderMethodNameStartsWithTestTest::testProvider)
This test did not perform any assertions
Test Finished (PHPUnit\TestFixture\DataProviderMethodNameStartsWithTestTest::testProvider)
Test Suite Started (PHPUnit\TestFixture\DataProviderMethodNameStartsWithTestTest::testOne, 1 test)
Test Preparation Started (PHPUnit\TestFixture\DataProviderMethodNameStartsWithTestTest::testOne#0)
Test Prepared (PHPUnit\TestFixture\DataProviderMethodNameStartsWithTestTest::testOne#0)
Test Passed (PHPUnit\TestFixture\DataProviderMethodNameStartsWithTestTest::testOne#0)
Test Finished (PHPUnit\TestFixture\DataProviderMethodNameStartsWithTestTest::testOne#0)
Test Suite Finished (PHPUnit\TestFixture\DataProviderMethodNameStartsWithTestTest::testOne, 1 test)
Test Suite Finished (PHPUnit\TestFixture\DataProviderMethodNameStartsWithTestTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
