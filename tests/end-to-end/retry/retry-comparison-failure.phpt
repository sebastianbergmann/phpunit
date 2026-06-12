--TEST--
#[Retry] preserves the comparison failure of a failed attempt
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/ComparisonFailureOnFirstAttemptTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Retry\ComparisonFailureOnFirstAttemptTest, 1 test)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\ComparisonFailureOnFirstAttemptTest::testOne, up to 2 attempts)
Test Attempt Failed (PHPUnit\TestFixture\Retry\ComparisonFailureOnFirstAttemptTest::testOne)
Failed asserting that two strings are identical.
Test Preparation Started (PHPUnit\TestFixture\Retry\ComparisonFailureOnFirstAttemptTest::testOne (attempt 2 of 2))
Test Prepared (PHPUnit\TestFixture\Retry\ComparisonFailureOnFirstAttemptTest::testOne (attempt 2 of 2))
Test Passed (PHPUnit\TestFixture\Retry\ComparisonFailureOnFirstAttemptTest::testOne (attempt 2 of 2))
Test Finished (PHPUnit\TestFixture\Retry\ComparisonFailureOnFirstAttemptTest::testOne (attempt 2 of 2))
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\ComparisonFailureOnFirstAttemptTest::testOne, up to 2 attempts)
Test Suite Finished (PHPUnit\TestFixture\Retry\ComparisonFailureOnFirstAttemptTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
