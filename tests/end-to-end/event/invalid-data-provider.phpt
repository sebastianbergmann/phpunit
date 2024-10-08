--TEST--
The right events are emitted in the right order for a test that uses a data provider that returns an invalid array
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/InvalidDataProviderTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\Event\InvalidDataProviderTest::provider for test method PHPUnit\TestFixture\Event\InvalidDataProviderTest::testOne)
Data Provider Method Finished for PHPUnit\TestFixture\Event\InvalidDataProviderTest::testOne:
- PHPUnit\TestFixture\Event\InvalidDataProviderTest::provider
Test Triggered PHPUnit Error (PHPUnit\TestFixture\Event\InvalidDataProviderTest::testOne)
The data provider specified for PHPUnit\TestFixture\Event\InvalidDataProviderTest::testOne is invalid
Data set #0 is invalid, expected array but got int
Test Runner Triggered Warning (No tests found in class "PHPUnit\TestFixture\Event\InvalidDataProviderTest".)
Test Suite Loaded (0 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (0 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
