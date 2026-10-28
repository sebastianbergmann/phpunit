--TEST--
An assertion failure in an "after test" method of a test that failed because of an exception of a registered failure type does not emit a second outcome for the test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RegisteredFailureAndAssertionFailureInTearDownTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\RegisteredFailureAndAssertionFailureInTearDownTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\RegisteredFailureAndAssertionFailureInTearDownTest::testOne)
Before Test Method Called (PHPUnit\TestFixture\Event\RegisteredFailureAndAssertionFailureInTearDownTest::setUp)
Before Test Method Finished:
- PHPUnit\TestFixture\Event\RegisteredFailureAndAssertionFailureInTearDownTest::setUp
Test Prepared (PHPUnit\TestFixture\Event\RegisteredFailureAndAssertionFailureInTearDownTest::testOne)
Test Failed (PHPUnit\TestFixture\Event\RegisteredFailureAndAssertionFailureInTearDownTest::testOne)
message
After Test Method Called (PHPUnit\TestFixture\Event\RegisteredFailureAndAssertionFailureInTearDownTest::tearDown)
After Test Method Failed (PHPUnit\TestFixture\Event\RegisteredFailureAndAssertionFailureInTearDownTest::tearDown)
Failed asserting that false is true.
After Test Method Finished:
- PHPUnit\TestFixture\Event\RegisteredFailureAndAssertionFailureInTearDownTest::tearDown
Test Finished (PHPUnit\TestFixture\Event\RegisteredFailureAndAssertionFailureInTearDownTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\RegisteredFailureAndAssertionFailureInTearDownTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
