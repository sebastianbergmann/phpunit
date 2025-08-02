--TEST--
The right events are emitted in the right order for a test that uses a data provider that does not return an iterable
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DataProviderNotIterableTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\Event\DataProviderNotIterableTest::provider for test method PHPUnit\TestFixture\Event\DataProviderNotIterableTest::testSomething)
Data Provider Method Finished for PHPUnit\TestFixture\Event\DataProviderNotIterableTest::testSomething:
- PHPUnit\TestFixture\Event\DataProviderNotIterableTest::provider
Test Triggered PHPUnit Error (PHPUnit\TestFixture\Event\DataProviderNotIterableTest::testSomething)
The data provider PHPUnit\TestFixture\Event\DataProviderNotIterableTest::provider specified for PHPUnit\TestFixture\Event\DataProviderNotIterableTest::testSomething is invalid
Data Provider method PHPUnit\TestFixture\Event\DataProviderNotIterableTest::provider() does not return an iterable
Test Runner Triggered Warning (No tests found in class "PHPUnit\TestFixture\Event\DataProviderNotIterableTest".)
Test Suite Loaded (0 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (0 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
