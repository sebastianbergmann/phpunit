--TEST--
#[Retry] combined with #[Repeat] triggers a test runner warning and is ignored
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RetryCombinedWithRepeatTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Runner Triggered PHPUnit Warning (Method PHPUnit\TestFixture\Retry\RetryCombinedWithRepeatTest::testOne is annotated with both #[Repeat] and #[Retry], the #[Retry] attribute is ignored)
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Retry\RetryCombinedWithRepeatTest, 2 tests)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Retry\RetryCombinedWithRepeatTest::testOne, 2 repetitions)
Test Preparation Started (PHPUnit\TestFixture\Retry\RetryCombinedWithRepeatTest::testOne (repetition 1 of 2))
Test Prepared (PHPUnit\TestFixture\Retry\RetryCombinedWithRepeatTest::testOne (repetition 1 of 2))
Test Passed (PHPUnit\TestFixture\Retry\RetryCombinedWithRepeatTest::testOne (repetition 1 of 2))
Test Finished (PHPUnit\TestFixture\Retry\RetryCombinedWithRepeatTest::testOne (repetition 1 of 2))
Test Preparation Started (PHPUnit\TestFixture\Retry\RetryCombinedWithRepeatTest::testOne (repetition 2 of 2))
Test Prepared (PHPUnit\TestFixture\Retry\RetryCombinedWithRepeatTest::testOne (repetition 2 of 2))
Test Passed (PHPUnit\TestFixture\Retry\RetryCombinedWithRepeatTest::testOne (repetition 2 of 2))
Test Finished (PHPUnit\TestFixture\Retry\RetryCombinedWithRepeatTest::testOne (repetition 2 of 2))
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Retry\RetryCombinedWithRepeatTest::testOne, 2 repetitions)
Test Suite Finished (PHPUnit\TestFixture\Retry\RetryCombinedWithRepeatTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
