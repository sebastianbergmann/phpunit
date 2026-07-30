--TEST--
#[Retry] with a maximum number of attempts that is not a positive integer does not suppress the --repeat CLI option
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RetryAttributeInvalidMaxAttemptsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Runner Triggered PHPUnit Warning (Method PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne is annotated with #[Retry] but 0 is not a positive integer for the maximum number of attempts and will not be retried)
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest, 3 tests)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne, 3 repetitions)
Test Preparation Started (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne (repetition 1 of 3))
Test Prepared (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne (repetition 1 of 3))
Test Passed (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne (repetition 1 of 3))
Test Finished (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne (repetition 1 of 3))
Test Preparation Started (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne (repetition 2 of 3))
Test Prepared (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne (repetition 2 of 3))
Test Passed (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne (repetition 2 of 3))
Test Finished (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne (repetition 2 of 3))
Test Preparation Started (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne (repetition 3 of 3))
Test Prepared (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne (repetition 3 of 3))
Test Passed (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne (repetition 3 of 3))
Test Finished (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne (repetition 3 of 3))
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne, 3 repetitions)
Test Suite Finished (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
