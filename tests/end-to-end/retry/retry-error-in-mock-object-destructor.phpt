--TEST--
#[Retry] retries test whose mock object raises an exception in its destructor
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/ErrorInMockObjectDestructorTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Retry\ErrorInMockObjectDestructorTest, 1 test)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\ErrorInMockObjectDestructorTest::testOne, up to 2 attempts)
Test Attempt Errored (PHPUnit\TestFixture\Retry\ErrorInMockObjectDestructorTest::testOne)
Error in destructor of mock object on first attempt
Test Preparation Started (PHPUnit\TestFixture\Retry\ErrorInMockObjectDestructorTest::testOne (attempt 2 of 2))
Test Prepared (PHPUnit\TestFixture\Retry\ErrorInMockObjectDestructorTest::testOne (attempt 2 of 2))
Mock Object Created (PHPUnit\TestFixture\Retry\ClassWhoseDestructorThrowsOnFirstAttempt)
Test Passed (PHPUnit\TestFixture\Retry\ErrorInMockObjectDestructorTest::testOne (attempt 2 of 2))
Test Finished (PHPUnit\TestFixture\Retry\ErrorInMockObjectDestructorTest::testOne (attempt 2 of 2))
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\ErrorInMockObjectDestructorTest::testOne, up to 2 attempts)
Test Suite Finished (PHPUnit\TestFixture\Retry\ErrorInMockObjectDestructorTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
