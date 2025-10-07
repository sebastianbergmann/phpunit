--TEST--
The right events are emitted in the right order for a test that uses a data provider that provides no data
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DataProviderInParentTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\Event\DataProviderInParentTest::data_provider for test method PHPUnit\TestFixture\Event\DataProviderInParentTest::testSomething)
Data Provider Method Finished for PHPUnit\TestFixture\Event\DataProviderInParentTest::testSomething:
- PHPUnit\TestFixture\Event\DataProviderInParentTest::data_provider
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\DataProviderInParentTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\Event\DataProviderInParentTest::testSomething, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\DataProviderInParentTest::testSomething#0)
Test Prepared (PHPUnit\TestFixture\Event\DataProviderInParentTest::testSomething#0)
Test Passed (PHPUnit\TestFixture\Event\DataProviderInParentTest::testSomething#0)
Test Finished (PHPUnit\TestFixture\Event\DataProviderInParentTest::testSomething#0)
Test Suite Finished (PHPUnit\TestFixture\Event\DataProviderInParentTest::testSomething, 1 test)
Test Suite Finished (PHPUnit\TestFixture\Event\DataProviderInParentTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
