--TEST--
#[Retry] with a maximum of one attempt runs the test once as a regular test without triggering a warning
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RetryAttributeOneMaxAttemptTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Retry\RetryAttributeOneMaxAttemptTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Retry\RetryAttributeOneMaxAttemptTest::testOne)
Test Prepared (PHPUnit\TestFixture\Retry\RetryAttributeOneMaxAttemptTest::testOne)
Test Passed (PHPUnit\TestFixture\Retry\RetryAttributeOneMaxAttemptTest::testOne)
Test Finished (PHPUnit\TestFixture\Retry\RetryAttributeOneMaxAttemptTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Retry\RetryAttributeOneMaxAttemptTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
