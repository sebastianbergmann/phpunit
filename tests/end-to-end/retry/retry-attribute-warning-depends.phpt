--TEST--
#[Retry] on method that depends on another test triggers a test runner warning
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RetryAttributeOnDependentTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Runner Triggered PHPUnit Warning (Method PHPUnit\TestFixture\Retry\RetryAttributeOnDependentTest::testTwo is annotated with #[Retry] but depends on another test and will not be retried)
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Retry\RetryAttributeOnDependentTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Retry\RetryAttributeOnDependentTest::testOne)
Test Prepared (PHPUnit\TestFixture\Retry\RetryAttributeOnDependentTest::testOne)
Test Passed (PHPUnit\TestFixture\Retry\RetryAttributeOnDependentTest::testOne)
Test Finished (PHPUnit\TestFixture\Retry\RetryAttributeOnDependentTest::testOne)
Test Preparation Started (PHPUnit\TestFixture\Retry\RetryAttributeOnDependentTest::testTwo)
Test Prepared (PHPUnit\TestFixture\Retry\RetryAttributeOnDependentTest::testTwo)
Test Passed (PHPUnit\TestFixture\Retry\RetryAttributeOnDependentTest::testTwo)
Test Finished (PHPUnit\TestFixture\Retry\RetryAttributeOnDependentTest::testTwo)
Test Suite Finished (PHPUnit\TestFixture\Retry\RetryAttributeOnDependentTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
