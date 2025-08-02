--TEST--
The right events are emitted in the right order for a test that uses a data provider that provides data with invalid keys
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DataProviderInvalidKeyTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\Event\DataProviderInvalidKeyTest::provider for test method PHPUnit\TestFixture\Event\DataProviderInvalidKeyTest::testOne)
Data Provider Method Finished for PHPUnit\TestFixture\Event\DataProviderInvalidKeyTest::testOne:
- PHPUnit\TestFixture\Event\DataProviderInvalidKeyTest::provider
Test Triggered PHPUnit Error (PHPUnit\TestFixture\Event\DataProviderInvalidKeyTest::testOne)
The data provider specified for PHPUnit\TestFixture\Event\DataProviderInvalidKeyTest::testOne is invalid
The key must be an integer or a string, float given
Test Runner Triggered Warning (No tests found in class "PHPUnit\TestFixture\Event\DataProviderInvalidKeyTest".)
Test Suite Loaded (0 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (0 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
