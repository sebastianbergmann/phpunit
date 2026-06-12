--TEST--
--stop-on-failure aborts the test run after a retried test exhausts all attempts
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--stop-on-failure';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/StopOnFailureTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Retry\StopOnFailureTest, 2 tests)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\StopOnFailureTest::testOne, up to 2 attempts)
Test Attempt Failed (PHPUnit\TestFixture\Retry\StopOnFailureTest::testOne)
Failure on every attempt
Test Preparation Started (PHPUnit\TestFixture\Retry\StopOnFailureTest::testOne (attempt 2 of 2))
Test Prepared (PHPUnit\TestFixture\Retry\StopOnFailureTest::testOne (attempt 2 of 2))
Test Failed (PHPUnit\TestFixture\Retry\StopOnFailureTest::testOne (attempt 2 of 2))
Failure on every attempt
Test Finished (PHPUnit\TestFixture\Retry\StopOnFailureTest::testOne (attempt 2 of 2))
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\StopOnFailureTest::testOne, up to 2 attempts)
Test Runner Execution Aborted
Test Suite Finished (PHPUnit\TestFixture\Retry\StopOnFailureTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
