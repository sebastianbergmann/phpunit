--TEST--
--retry with invalid value triggers warning
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--retry';
$_SERVER['argv'][] = '0';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/CliRetryPlainTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Triggered PHPUnit Warning (Option "--retry 0" ignored because "0" is not a positive integer)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Retry\CliRetryPlainTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Retry\CliRetryPlainTest::testOne)
Test Prepared (PHPUnit\TestFixture\Retry\CliRetryPlainTest::testOne)
Test Passed (PHPUnit\TestFixture\Retry\CliRetryPlainTest::testOne)
Test Finished (PHPUnit\TestFixture\Retry\CliRetryPlainTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\Retry\CliRetryPlainTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
