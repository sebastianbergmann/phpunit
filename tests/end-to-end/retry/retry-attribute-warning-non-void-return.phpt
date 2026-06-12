--TEST--
#[Retry] on method with non-void return type triggers a test runner warning
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RetryAttributeOnNonVoidReturnTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Runner Triggered PHPUnit Warning (Method PHPUnit\TestFixture\Retry\RetryAttributeOnNonVoidReturnTest::testWithReturnValue is annotated with #[Retry] but does not have a void return type declaration and will not be retried)
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Retry\RetryAttributeOnNonVoidReturnTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Retry\RetryAttributeOnNonVoidReturnTest::testWithReturnValue)
Test Prepared (PHPUnit\TestFixture\Retry\RetryAttributeOnNonVoidReturnTest::testWithReturnValue)
Test Passed (PHPUnit\TestFixture\Retry\RetryAttributeOnNonVoidReturnTest::testWithReturnValue)
Test Finished (PHPUnit\TestFixture\Retry\RetryAttributeOnNonVoidReturnTest::testWithReturnValue)
Test Suite Finished (PHPUnit\TestFixture\Retry\RetryAttributeOnNonVoidReturnTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
