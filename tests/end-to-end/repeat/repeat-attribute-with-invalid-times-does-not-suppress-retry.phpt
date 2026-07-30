--TEST--
#[Repeat] with a number of repetitions that is not a positive integer does not suppress the --retry CLI option
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--retry';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RepeatAttributeInvalidTimesFailureTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Runner Triggered PHPUnit Warning (Method PHPUnit\TestFixture\Repeat\RepeatAttributeInvalidTimesFailureTest::testOne is annotated with #[Repeat] but 0 is not a positive integer for the number of repetitions and will not be repeated)
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Repeat\RepeatAttributeInvalidTimesFailureTest, 1 test)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Repeat\RepeatAttributeInvalidTimesFailureTest::testOne, up to 3 attempts)
Test Attempt Failed (PHPUnit\TestFixture\Repeat\RepeatAttributeInvalidTimesFailureTest::testOne)
Failed asserting that false is true.
Test Attempt Failed (PHPUnit\TestFixture\Repeat\RepeatAttributeInvalidTimesFailureTest::testOne (attempt 2 of 3))
Failed asserting that false is true.
Test Preparation Started (PHPUnit\TestFixture\Repeat\RepeatAttributeInvalidTimesFailureTest::testOne (attempt 3 of 3))
Test Prepared (PHPUnit\TestFixture\Repeat\RepeatAttributeInvalidTimesFailureTest::testOne (attempt 3 of 3))
Test Failed (PHPUnit\TestFixture\Repeat\RepeatAttributeInvalidTimesFailureTest::testOne (attempt 3 of 3))
Failed asserting that false is true.
Test Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeInvalidTimesFailureTest::testOne (attempt 3 of 3))
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeInvalidTimesFailureTest::testOne, up to 3 attempts)
Test Suite Finished (PHPUnit\TestFixture\Repeat\RepeatAttributeInvalidTimesFailureTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
