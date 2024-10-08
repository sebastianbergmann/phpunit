--TEST--
The right events are emitted in the right order for a successful test that uses a data provider
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DataProviderTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\Event\DataProviderTest::values for test method PHPUnit\TestFixture\Event\DataProviderTest::testSuccess)
Data Provider Method Finished for PHPUnit\TestFixture\Event\DataProviderTest::testSuccess:
- PHPUnit\TestFixture\Event\DataProviderTest::values
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Event\DataProviderTest, 2 tests)
Test Suite Started (PHPUnit\TestFixture\Event\DataProviderTest::testSuccess, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\DataProviderTest::testSuccess#0)
Test Prepared (PHPUnit\TestFixture\Event\DataProviderTest::testSuccess#0)
Test Passed (PHPUnit\TestFixture\Event\DataProviderTest::testSuccess#0)
Test Finished (PHPUnit\TestFixture\Event\DataProviderTest::testSuccess#0)
Test Preparation Started (PHPUnit\TestFixture\Event\DataProviderTest::testSuccess#1)
Test Prepared (PHPUnit\TestFixture\Event\DataProviderTest::testSuccess#1)
Test Passed (PHPUnit\TestFixture\Event\DataProviderTest::testSuccess#1)
Test Finished (PHPUnit\TestFixture\Event\DataProviderTest::testSuccess#1)
Test Suite Finished (PHPUnit\TestFixture\Event\DataProviderTest::testSuccess, 2 tests)
Test Suite Finished (PHPUnit\TestFixture\Event\DataProviderTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
