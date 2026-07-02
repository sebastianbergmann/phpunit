--TEST--
--repeat skips dependent test when a later repetition of the test it depends on fails after an earlier repetition passed
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DependsOnFailureInSecondRepetitionTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Repeat\DependsOnFailureInSecondRepetitionTest, 3 tests)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Repeat\DependsOnFailureInSecondRepetitionTest::testOne, 2 repetitions)
Test Preparation Started (PHPUnit\TestFixture\Repeat\DependsOnFailureInSecondRepetitionTest::testOne (repetition 1 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\DependsOnFailureInSecondRepetitionTest::testOne (repetition 1 of 2))
Test Passed (PHPUnit\TestFixture\Repeat\DependsOnFailureInSecondRepetitionTest::testOne (repetition 1 of 2))
Test Finished (PHPUnit\TestFixture\Repeat\DependsOnFailureInSecondRepetitionTest::testOne (repetition 1 of 2))
Test Preparation Started (PHPUnit\TestFixture\Repeat\DependsOnFailureInSecondRepetitionTest::testOne (repetition 2 of 2))
Test Prepared (PHPUnit\TestFixture\Repeat\DependsOnFailureInSecondRepetitionTest::testOne (repetition 2 of 2))
Test Failed (PHPUnit\TestFixture\Repeat\DependsOnFailureInSecondRepetitionTest::testOne (repetition 2 of 2))
Failure on second repetition
Test Finished (PHPUnit\TestFixture\Repeat\DependsOnFailureInSecondRepetitionTest::testOne (repetition 2 of 2))
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Repeat\DependsOnFailureInSecondRepetitionTest::testOne, 2 repetitions)
Test Skipped (PHPUnit\TestFixture\Repeat\DependsOnFailureInSecondRepetitionTest::testTwo)
This test depends on "PHPUnit\TestFixture\Repeat\DependsOnFailureInSecondRepetitionTest::testOne" to pass
Test Suite Finished (PHPUnit\TestFixture\Repeat\DependsOnFailureInSecondRepetitionTest, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
