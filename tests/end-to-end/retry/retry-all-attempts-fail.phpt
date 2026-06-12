--TEST--
#[Retry] reports failure of the final attempt when all attempts fail
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/AllAttemptsFailTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Retry\AllAttemptsFailTest, 1 test)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\AllAttemptsFailTest::testOne, up to 3 attempts)
Test Attempt Failed (PHPUnit\TestFixture\Retry\AllAttemptsFailTest::testOne)
Failure on attempt 1
Test Attempt Failed (PHPUnit\TestFixture\Retry\AllAttemptsFailTest::testOne (attempt 2 of 3))
Failure on attempt 2
Test Preparation Started (PHPUnit\TestFixture\Retry\AllAttemptsFailTest::testOne (attempt 3 of 3))
Test Prepared (PHPUnit\TestFixture\Retry\AllAttemptsFailTest::testOne (attempt 3 of 3))
Test Failed (PHPUnit\TestFixture\Retry\AllAttemptsFailTest::testOne (attempt 3 of 3))
Failure on attempt 3
Test Finished (PHPUnit\TestFixture\Retry\AllAttemptsFailTest::testOne (attempt 3 of 3))
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\AllAttemptsFailTest::testOne, up to 3 attempts)
Test Suite Finished (PHPUnit\TestFixture\Retry\AllAttemptsFailTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
