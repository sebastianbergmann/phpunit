--TEST--
The right events are emitted in the right order for a test with a successful expectation on a mock object
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/SuccessfulExpectationTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\SuccessfulExpectationTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\SuccessfulExpectationTest::testOne)
Test Prepared (PHPUnit\TestFixture\Event\SuccessfulExpectationTest::testOne)
Mock Object Created (PHPUnit\TestFixture\MockObject\AnInterface)
Test Passed (PHPUnit\TestFixture\Event\SuccessfulExpectationTest::testOne)
Test Finished (PHPUnit\TestFixture\Event\SuccessfulExpectationTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Event\SuccessfulExpectationTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
