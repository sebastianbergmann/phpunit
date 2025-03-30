--TEST--
The right events are emitted in the right order for a test that uses a data provider that returns an invalid array
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/InvalidDataProviderWithOneTestPassingTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Data Provider Method Called (PHPUnit\TestFixture\Event\InvalidDataProviderWithOneTestPassingTest::provider for test method PHPUnit\TestFixture\Event\InvalidDataProviderWithOneTestPassingTest::testOne)
Data Provider Method Finished for PHPUnit\TestFixture\Event\InvalidDataProviderWithOneTestPassingTest::testOne:
- PHPUnit\TestFixture\Event\InvalidDataProviderWithOneTestPassingTest::provider
Test Triggered PHPUnit Error (PHPUnit\TestFixture\Event\InvalidDataProviderWithOneTestPassingTest::testOne)
The data provider specified for PHPUnit\TestFixture\Event\InvalidDataProviderWithOneTestPassingTest::testOne is invalid
Data set #0 is invalid, expected array but got int
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\InvalidDataProviderWithOneTestPassingTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\InvalidDataProviderWithOneTestPassingTest::testTwo)
Test Prepared (PHPUnit\TestFixture\Event\InvalidDataProviderWithOneTestPassingTest::testTwo)
Test Passed (PHPUnit\TestFixture\Event\InvalidDataProviderWithOneTestPassingTest::testTwo)
Test Finished (PHPUnit\TestFixture\Event\InvalidDataProviderWithOneTestPassingTest::testTwo)
Test Suite Finished (PHPUnit\TestFixture\Event\InvalidDataProviderWithOneTestPassingTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
