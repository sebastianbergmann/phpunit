--TEST--
#[Retry] with a maximum number of attempts that is not a positive integer triggers a test runner warning
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RetryAttributeInvalidMaxAttemptsTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Runner Triggered PHPUnit Warning (Method PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne is annotated with #[Retry] but 0 is not a positive integer for the maximum number of attempts and will not be retried)
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne)
Test Prepared (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne)
Test Passed (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne)
Test Finished (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Retry\RetryAttributeInvalidMaxAttemptsTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
