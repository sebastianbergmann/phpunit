--TEST--
#[Repeat] skips remaining repetitions when the failure threshold is reached
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/FailureThresholdReachedTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest, 5 tests)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne, 5 repetitions)
Test Preparation Started (PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne (repetition 1 of 5))
Test Prepared (PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne (repetition 1 of 5))
Test Failed (PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne (repetition 1 of 5))
Failure on repetition 1
Test Finished (PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne (repetition 1 of 5))
Test Preparation Started (PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne (repetition 2 of 5))
Test Prepared (PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne (repetition 2 of 5))
Test Failed (PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne (repetition 2 of 5))
Failure on repetition 2
Test Finished (PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne (repetition 2 of 5))
Test Skipped (PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne (repetition 3 of 5))
Remaining repetition skipped after failure in repetition 2
Test Skipped (PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne (repetition 4 of 5))
Remaining repetition skipped after failure in repetition 2
Test Skipped (PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne (repetition 5 of 5))
Remaining repetition skipped after failure in repetition 2
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest::testOne, 5 repetitions)
Test Suite Finished (PHPUnit\TestFixture\Repeat\FailureThresholdReachedTest, 5 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
