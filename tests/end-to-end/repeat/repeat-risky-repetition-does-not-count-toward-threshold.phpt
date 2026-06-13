--TEST--
#[Repeat] runs all repetitions when a repetition is considered risky, risky repetitions do not count toward the failure threshold
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RiskyRepetitionTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Repeat\RiskyRepetitionTest, 3 tests)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Repeat\RiskyRepetitionTest::testOne, 3 repetitions)
Test Preparation Started (PHPUnit\TestFixture\Repeat\RiskyRepetitionTest::testOne (repetition 1 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\RiskyRepetitionTest::testOne (repetition 1 of 3))
Test Passed (PHPUnit\TestFixture\Repeat\RiskyRepetitionTest::testOne (repetition 1 of 3))
Test Finished (PHPUnit\TestFixture\Repeat\RiskyRepetitionTest::testOne (repetition 1 of 3))
Test Preparation Started (PHPUnit\TestFixture\Repeat\RiskyRepetitionTest::testOne (repetition 2 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\RiskyRepetitionTest::testOne (repetition 2 of 3))
Test Passed (PHPUnit\TestFixture\Repeat\RiskyRepetitionTest::testOne (repetition 2 of 3))
Test Considered Risky (PHPUnit\TestFixture\Repeat\RiskyRepetitionTest::testOne (repetition 2 of 3))
This test did not perform any assertions
Test Finished (PHPUnit\TestFixture\Repeat\RiskyRepetitionTest::testOne (repetition 2 of 3))
Test Preparation Started (PHPUnit\TestFixture\Repeat\RiskyRepetitionTest::testOne (repetition 3 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\RiskyRepetitionTest::testOne (repetition 3 of 3))
Test Passed (PHPUnit\TestFixture\Repeat\RiskyRepetitionTest::testOne (repetition 3 of 3))
Test Finished (PHPUnit\TestFixture\Repeat\RiskyRepetitionTest::testOne (repetition 3 of 3))
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Repeat\RiskyRepetitionTest::testOne, 3 repetitions)
Test Suite Finished (PHPUnit\TestFixture\Repeat\RiskyRepetitionTest, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
