--TEST--
#[Retry] in combination with #[RunInSeparateProcess] reports failure of the final attempt when all attempts fail
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/RetryInSeparateProcessAllAttemptsFailTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Retry\RetryInSeparateProcessAllAttemptsFailTest, 1 test)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\RetryInSeparateProcessAllAttemptsFailTest::testOne, up to 2 attempts)
Test Attempt Failed (PHPUnit\TestFixture\Retry\RetryInSeparateProcessAllAttemptsFailTest::testOne)
Failure in child process
Child Process Started (test requiring process isolation)
Test Preparation Started (PHPUnit\TestFixture\Retry\RetryInSeparateProcessAllAttemptsFailTest::testOne (attempt 2 of 2))
Test Prepared (PHPUnit\TestFixture\Retry\RetryInSeparateProcessAllAttemptsFailTest::testOne (attempt 2 of 2))
Test Failed (PHPUnit\TestFixture\Retry\RetryInSeparateProcessAllAttemptsFailTest::testOne (attempt 2 of 2))
Failure in child process
Test Finished (PHPUnit\TestFixture\Retry\RetryInSeparateProcessAllAttemptsFailTest::testOne (attempt 2 of 2))
Child Process Finished (test requiring process isolation)
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\RetryInSeparateProcessAllAttemptsFailTest::testOne, up to 2 attempts)
Test Suite Finished (PHPUnit\TestFixture\Retry\RetryInSeparateProcessAllAttemptsFailTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
