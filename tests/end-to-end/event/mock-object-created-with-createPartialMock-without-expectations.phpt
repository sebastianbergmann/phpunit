--TEST--
The right events are emitted in the right order for a test that creates a mock object using createPartialMock() and does not configure expectations
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--bootstrap';
$_SERVER['argv'][] = __DIR__ . '/_files/Example.php';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/CreatePartialMockWithoutExpectationsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Bootstrap Finished (%sExample.php)
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (TestFixture\PHPUnit\Event\CreatePartialMockWithoutExpectationsTest, 1 test)
Test Preparation Started (TestFixture\PHPUnit\Event\CreatePartialMockWithoutExpectationsTest::testOne)
Test Prepared (TestFixture\PHPUnit\Event\CreatePartialMockWithoutExpectationsTest::testOne)
Partial Mock Object Created (PHPUnit\TestFixture\Event\Example)
Test Triggered PHPUnit Notice (TestFixture\PHPUnit\Event\CreatePartialMockWithoutExpectationsTest::testOne)
No expectations were configured for the mock object for PHPUnit\TestFixture\Event\Example. Consider refactoring your test code to use a test stub instead. The #[AllowMockObjectsWithoutExpectations] attribute can be used to opt out of this check.
Test Passed (TestFixture\PHPUnit\Event\CreatePartialMockWithoutExpectationsTest::testOne)
Test Finished (TestFixture\PHPUnit\Event\CreatePartialMockWithoutExpectationsTest::testOne)
Test Suite Finished (TestFixture\PHPUnit\Event\CreatePartialMockWithoutExpectationsTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
