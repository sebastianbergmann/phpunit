--TEST--
An exception in an "after test" method of a test that failed because of an exception of a registered failure type does not emit a second outcome for the test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RegisteredFailureAndExceptionInTearDownTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Event\RegisteredFailureAndExceptionInTearDownTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\RegisteredFailureAndExceptionInTearDownTest::testOne)
Before Test Method Called (PHPUnit\TestFixture\Event\RegisteredFailureAndExceptionInTearDownTest::setUp)
Before Test Method Finished:
- PHPUnit\TestFixture\Event\RegisteredFailureAndExceptionInTearDownTest::setUp
Test Prepared (PHPUnit\TestFixture\Event\RegisteredFailureAndExceptionInTearDownTest::testOne)
Test Failed (PHPUnit\TestFixture\Event\RegisteredFailureAndExceptionInTearDownTest::testOne)
message
After Test Method Called (PHPUnit\TestFixture\Event\RegisteredFailureAndExceptionInTearDownTest::tearDown)
After Test Method Errored (PHPUnit\TestFixture\Event\RegisteredFailureAndExceptionInTearDownTest::tearDown)
message
After Test Method Finished:
- PHPUnit\TestFixture\Event\RegisteredFailureAndExceptionInTearDownTest::tearDown
Test Finished (PHPUnit\TestFixture\Event\RegisteredFailureAndExceptionInTearDownTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\RegisteredFailureAndExceptionInTearDownTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
