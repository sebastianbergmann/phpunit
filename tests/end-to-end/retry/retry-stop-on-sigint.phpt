--TEST--
#[Retry] does not retry a failed test when test execution was interrupted by a signal
--SKIPIF--
<?php declare(strict_types=1);
if (!extension_loaded('pcntl')) echo 'skip: Extension pcntl is required';
if (!extension_loaded('posix')) echo 'skip: Extension posix is required';
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/InterruptTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Retry\InterruptTest, 1 test)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\InterruptTest::testOne, up to 3 attempts)
Test Attempt Failed (PHPUnit\TestFixture\Retry\InterruptTest::testOne)
Failure on first attempt
Test Runner Execution Aborted
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\InterruptTest::testOne, up to 3 attempts)
Test Suite Finished (PHPUnit\TestFixture\Retry\InterruptTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
Test execution was interrupted by a signal.
PHPUnit Finished (Shell Exit Code: 0)
