--TEST--
#[Retry] in combination with #[RunInSeparateProcess] retries a test whose child process crashed
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RetryInSeparateProcessCrashTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Retry\RetryInSeparateProcessCrashTest, 1 test)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\RetryInSeparateProcessCrashTest::testOne, up to 3 attempts)
Test Attempt Errored (PHPUnit\TestFixture\Retry\RetryInSeparateProcessCrashTest::testOne)
Test was run in child process and ended unexpectedly
Test Attempt Errored (PHPUnit\TestFixture\Retry\RetryInSeparateProcessCrashTest::testOne (attempt 2 of 3))
Test was run in child process and ended unexpectedly
Child Process Started (test requiring process isolation)
Child Process Errored (test requiring process isolation)
Test Errored (PHPUnit\TestFixture\Retry\RetryInSeparateProcessCrashTest::testOne (attempt 3 of 3))
Test was run in child process and ended unexpectedly
Test Finished (PHPUnit\TestFixture\Retry\RetryInSeparateProcessCrashTest::testOne (attempt 3 of 3))
Child Process Finished (test requiring process isolation)
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\RetryInSeparateProcessCrashTest::testOne, up to 3 attempts)
Test Suite Finished (PHPUnit\TestFixture\Retry\RetryInSeparateProcessCrashTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
