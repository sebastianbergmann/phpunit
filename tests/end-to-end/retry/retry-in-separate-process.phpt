--TEST--
#[Retry] in combination with #[RunInSeparateProcess] retries the test in a new process
--FILE--
<?php declare(strict_types=1);
@unlink(sys_get_temp_dir() . '/phpunit-retry-in-separate-process-test.counter');

$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RetryInSeparateProcessTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--CLEAN--
<?php declare(strict_types=1);
@unlink(sys_get_temp_dir() . '/phpunit-retry-in-separate-process-test.counter');
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Retry\RetryInSeparateProcessTest, 1 test)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\RetryInSeparateProcessTest::testOne, up to 2 attempts)
Test Attempt Failed (PHPUnit\TestFixture\Retry\RetryInSeparateProcessTest::testOne)
Failed asserting that 1 is greater than 1.
Child Process Started (test requiring process isolation)
Test Preparation Started (PHPUnit\TestFixture\Retry\RetryInSeparateProcessTest::testOne (attempt 2 of 2))
Test Prepared (PHPUnit\TestFixture\Retry\RetryInSeparateProcessTest::testOne (attempt 2 of 2))
Test Passed (PHPUnit\TestFixture\Retry\RetryInSeparateProcessTest::testOne (attempt 2 of 2))
Test Finished (PHPUnit\TestFixture\Retry\RetryInSeparateProcessTest::testOne (attempt 2 of 2))
Child Process Finished (test requiring process isolation)
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\RetryInSeparateProcessTest::testOne, up to 2 attempts)
Test Suite Finished (PHPUnit\TestFixture\Retry\RetryInSeparateProcessTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
