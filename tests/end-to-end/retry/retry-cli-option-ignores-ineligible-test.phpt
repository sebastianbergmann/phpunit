--TEST--
The --retry CLI option silently ignores tests that are not eligible for retrying
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--retry';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/CliRetryNonVoidReturnTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Retry\CliRetryNonVoidReturnTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Retry\CliRetryNonVoidReturnTest::testOne)
Test Prepared (PHPUnit\TestFixture\Retry\CliRetryNonVoidReturnTest::testOne)
Test Passed (PHPUnit\TestFixture\Retry\CliRetryNonVoidReturnTest::testOne)
Test Finished (PHPUnit\TestFixture\Retry\CliRetryNonVoidReturnTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Retry\CliRetryNonVoidReturnTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
