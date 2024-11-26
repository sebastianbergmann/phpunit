--TEST--
The right events are emitted in the right order when a test that is not expected to perform assertions does not perform assertions and when a test that is not expected to perform assertions does perform assertions
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/ExpectedAndUnexpectedAssertionsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (4 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (4 tests)
Test Suite Started (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest, 4 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest::testOne)
Test Passed (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest::testTwo)
Test Prepared (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest::testTwo)
Test Passed (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest::testTwo)
Test Finished (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest::testTwo)
Test Preparation Started (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest::testThree)
Test Prepared (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest::testThree)
Test Passed (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest::testThree)
Test Considered Risky (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest::testThree)
This test is not expected to perform assertions but performed 1 assertion
Test Finished (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest::testThree)
Test Preparation Started (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest::testFour)
Test Prepared (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest::testFour)
Test Passed (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest::testFour)
Test Considered Risky (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest::testFour)
This test is not expected to perform assertions but performed 1 assertion
Test Finished (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest::testFour)
Test Suite Finished (PHPUnit\TestFixture\Event\ExpectedAndUnexpectedAssertionsTest, 4 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
