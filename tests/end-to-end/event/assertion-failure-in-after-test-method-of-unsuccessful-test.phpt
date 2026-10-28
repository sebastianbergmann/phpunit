--TEST--
An assertion failure in a "after test" method of a test that already failed or errored does not emit a second outcome for the test
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/AssertionFailureInTearDownOfUnsuccessfulTestTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Event\AssertionFailureInTearDownOfUnsuccessfulTestTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\AssertionFailureInTearDownOfUnsuccessfulTestTest::testFails)
Test Prepared (PHPUnit\TestFixture\Event\AssertionFailureInTearDownOfUnsuccessfulTestTest::testFails)
Test Failed (PHPUnit\TestFixture\Event\AssertionFailureInTearDownOfUnsuccessfulTestTest::testFails)
Failed asserting that false is true.
After Test Method Called (PHPUnit\TestFixture\Event\AssertionFailureInTearDownOfUnsuccessfulTestTest::afterTest)
After Test Method Failed (PHPUnit\TestFixture\Event\AssertionFailureInTearDownOfUnsuccessfulTestTest::afterTest)
Failed asserting that false is true.
After Test Method Finished:
- PHPUnit\TestFixture\Event\AssertionFailureInTearDownOfUnsuccessfulTestTest::afterTest
Test Finished (PHPUnit\TestFixture\Event\AssertionFailureInTearDownOfUnsuccessfulTestTest::testFails)
Test Preparation Started (PHPUnit\TestFixture\Event\AssertionFailureInTearDownOfUnsuccessfulTestTest::testErrors)
Test Prepared (PHPUnit\TestFixture\Event\AssertionFailureInTearDownOfUnsuccessfulTestTest::testErrors)
Test Errored (PHPUnit\TestFixture\Event\AssertionFailureInTearDownOfUnsuccessfulTestTest::testErrors)
message
After Test Method Called (PHPUnit\TestFixture\Event\AssertionFailureInTearDownOfUnsuccessfulTestTest::afterTest)
After Test Method Failed (PHPUnit\TestFixture\Event\AssertionFailureInTearDownOfUnsuccessfulTestTest::afterTest)
Failed asserting that false is true.
After Test Method Finished:
- PHPUnit\TestFixture\Event\AssertionFailureInTearDownOfUnsuccessfulTestTest::afterTest
Test Finished (PHPUnit\TestFixture\Event\AssertionFailureInTearDownOfUnsuccessfulTestTest::testErrors)
Test Suite Finished (PHPUnit\TestFixture\Event\AssertionFailureInTearDownOfUnsuccessfulTestTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
