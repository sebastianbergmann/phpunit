--TEST--
phpunit --filter testA#0-1 does not invoke data providers for non-matching methods
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testA#0-1';
$_SERVER['argv'][] = __DIR__ . '/../../_files/DataProviderSkipWhenFilteredTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::providerForA for test method PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::testA)
Data Provider Method Finished for PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::testA:
- PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::providerForA
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (2 tests)
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest, 2 tests)
Test Suite Started (PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::testA, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::testA#0)
Test Prepared (PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::testA#0)
Test Passed (PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::testA#0)
Test Finished (PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::testA#0)
Test Preparation Started (PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::testA#1)
Test Prepared (PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::testA#1)
Test Passed (PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::testA#1)
Test Finished (PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::testA#1)
Test Suite Finished (PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest::testA, 2 tests)
Test Suite Finished (PHPUnit\TestFixture\DataProviderSkipWhenFilteredTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
