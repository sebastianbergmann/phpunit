--TEST--
#[Retry] does not break #[Group] metadata when filtering by group
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--group';
$_SERVER['argv'][] = 'bar';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RetryWithGroupTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (1 test)
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Retry\RetryWithGroupTest, 1 test)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\RetryWithGroupTest::testRetried, up to 3 attempts)
Test Preparation Started (PHPUnit\TestFixture\Retry\RetryWithGroupTest::testRetried)
Test Prepared (PHPUnit\TestFixture\Retry\RetryWithGroupTest::testRetried)
Test Passed (PHPUnit\TestFixture\Retry\RetryWithGroupTest::testRetried)
Test Finished (PHPUnit\TestFixture\Retry\RetryWithGroupTest::testRetried)
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\RetryWithGroupTest::testRetried, up to 3 attempts)
Test Suite Finished (PHPUnit\TestFixture\Retry\RetryWithGroupTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
