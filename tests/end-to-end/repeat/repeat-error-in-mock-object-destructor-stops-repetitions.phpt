--TEST--
--repeat stops remaining repetitions after a mock object raised an exception in its destructor
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/ErrorInMockObjectDestructorOnSecondRepetitionTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\ErrorInMockObjectDestructorOnSecondRepetitionTest, 3 tests)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Repeat\ErrorInMockObjectDestructorOnSecondRepetitionTest::testOne, 3 repetitions)
Test Preparation Started (PHPUnit\TestFixture\Repeat\ErrorInMockObjectDestructorOnSecondRepetitionTest::testOne (repetition 1 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\ErrorInMockObjectDestructorOnSecondRepetitionTest::testOne (repetition 1 of 3))
Mock Object Created (PHPUnit\TestFixture\Repeat\ClassWhoseDestructorThrowsOnSecondRepetition)
Test Passed (PHPUnit\TestFixture\Repeat\ErrorInMockObjectDestructorOnSecondRepetitionTest::testOne (repetition 1 of 3))
Test Finished (PHPUnit\TestFixture\Repeat\ErrorInMockObjectDestructorOnSecondRepetitionTest::testOne (repetition 1 of 3))
Test Preparation Started (PHPUnit\TestFixture\Repeat\ErrorInMockObjectDestructorOnSecondRepetitionTest::testOne (repetition 2 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\ErrorInMockObjectDestructorOnSecondRepetitionTest::testOne (repetition 2 of 3))
Mock Object Created (PHPUnit\TestFixture\Repeat\ClassWhoseDestructorThrowsOnSecondRepetition)
Test Errored (PHPUnit\TestFixture\Repeat\ErrorInMockObjectDestructorOnSecondRepetitionTest::testOne (repetition 2 of 3))
Error in destructor of mock object on second repetition
Test Finished (PHPUnit\TestFixture\Repeat\ErrorInMockObjectDestructorOnSecondRepetitionTest::testOne (repetition 2 of 3))
Test Skipped (PHPUnit\TestFixture\Repeat\ErrorInMockObjectDestructorOnSecondRepetitionTest::testOne (repetition 3 of 3))
Remaining repetition skipped after failure in repetition 2
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Repeat\ErrorInMockObjectDestructorOnSecondRepetitionTest::testOne, 3 repetitions)
Test Suite Finished (PHPUnit\TestFixture\Repeat\ErrorInMockObjectDestructorOnSecondRepetitionTest, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
