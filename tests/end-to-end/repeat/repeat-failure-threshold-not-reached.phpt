--TEST--
#[Repeat] runs all repetitions when the number of failures stays below the failure threshold, failed repetitions are still reported as failures
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/FailureBelowThresholdTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest, 5 tests)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne, 5 repetitions)
Test Preparation Started (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 1 of 5))
Test Prepared (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 1 of 5))
Test Passed (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 1 of 5))
Test Finished (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 1 of 5))
Test Preparation Started (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 2 of 5))
Test Prepared (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 2 of 5))
Test Failed (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 2 of 5))
Failure on second repetition
Test Finished (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 2 of 5))
Test Preparation Started (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 3 of 5))
Test Prepared (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 3 of 5))
Test Passed (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 3 of 5))
Test Finished (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 3 of 5))
Test Preparation Started (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 4 of 5))
Test Prepared (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 4 of 5))
Test Passed (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 4 of 5))
Test Finished (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 4 of 5))
Test Preparation Started (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 5 of 5))
Test Prepared (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 5 of 5))
Test Passed (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 5 of 5))
Test Finished (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne (repetition 5 of 5))
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest::testOne, 5 repetitions)
Test Suite Finished (PHPUnit\TestFixture\Repeat\FailureBelowThresholdTest, 5 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
