--TEST--
--repeat does not block dependent test when a method with the same name fails in another class
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DependsBlockedByOtherClassFailingTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/DependsBlockedByOtherClassDependingTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (5 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (5 tests)
Test Suite Started (CLI Arguments, 5 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassFailingTest, 2 tests)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassFailingTest::testSomething, 2 repetitions)
Test Preparation Started (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassFailingTest::testSomething (repetition 1 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassFailingTest::testSomething (repetition 1 of 2))
Test Failed (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassFailingTest::testSomething (repetition 1 of 2))
Failure in unrelated class
Test Finished (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassFailingTest::testSomething (repetition 1 of 2))
Test Skipped (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassFailingTest::testSomething (repetition 2 of 2))
Remaining repetition skipped after failure in repetition 1
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassFailingTest::testSomething, 2 repetitions)
Test Suite Finished (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassFailingTest, 2 tests)
Test Suite Started (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassDependingTest, 3 tests)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassDependingTest::testSomething, 2 repetitions)
Test Preparation Started (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassDependingTest::testSomething (repetition 1 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassDependingTest::testSomething (repetition 1 of 2))
Test Passed (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassDependingTest::testSomething (repetition 1 of 2))
Test Finished (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassDependingTest::testSomething (repetition 1 of 2))
Test Preparation Started (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassDependingTest::testSomething (repetition 2 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassDependingTest::testSomething (repetition 2 of 2))
Test Passed (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassDependingTest::testSomething (repetition 2 of 2))
Test Finished (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassDependingTest::testSomething (repetition 2 of 2))
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassDependingTest::testSomething, 2 repetitions)
Test Preparation Started (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassDependingTest::testDependent)
Test Prepared (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassDependingTest::testDependent)
Test Passed (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassDependingTest::testDependent)
Test Finished (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassDependingTest::testDependent)
Test Suite Finished (PHPUnit\TestFixture\Repeat\DependsBlockedByOtherClassDependingTest, 3 tests)
Test Suite Finished (CLI Arguments, 5 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
