--TEST--
--repeat and --retry cannot be used together; --repeat takes precedence
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '2';
$_SERVER['argv'][] = '--retry';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/CliRetryPlainTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Triggered PHPUnit Warning (Options --repeat and --retry cannot be used together)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Retry\CliRetryPlainTest, 2 tests)
Test Suite for Repeated Test Method Started (PHPUnit\TestFixture\Retry\CliRetryPlainTest::testOne, 2 repetitions)
Test Preparation Started (PHPUnit\TestFixture\Retry\CliRetryPlainTest::testOne (repetition 1 of 2))
Test Prepared (PHPUnit\TestFixture\Retry\CliRetryPlainTest::testOne (repetition 1 of 2))
Test Passed (PHPUnit\TestFixture\Retry\CliRetryPlainTest::testOne (repetition 1 of 2))
Test Finished (PHPUnit\TestFixture\Retry\CliRetryPlainTest::testOne (repetition 1 of 2))
Test Preparation Started (PHPUnit\TestFixture\Retry\CliRetryPlainTest::testOne (repetition 2 of 2))
Test Prepared (PHPUnit\TestFixture\Retry\CliRetryPlainTest::testOne (repetition 2 of 2))
Test Passed (PHPUnit\TestFixture\Retry\CliRetryPlainTest::testOne (repetition 2 of 2))
Test Finished (PHPUnit\TestFixture\Retry\CliRetryPlainTest::testOne (repetition 2 of 2))
Test Suite for Repeated Test Method Finished (PHPUnit\TestFixture\Retry\CliRetryPlainTest::testOne, 2 repetitions)
Test Suite Finished (PHPUnit\TestFixture\Retry\CliRetryPlainTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
