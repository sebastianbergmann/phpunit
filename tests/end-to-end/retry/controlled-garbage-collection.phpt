--TEST--
The attempts of a retried test count toward the number of tests after which garbage is collected when the garbage collector is controlled
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = '--configuration';
$_SERVER['argv'][] = __DIR__ . '/_files/controlled-garbage-collection';

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
Test Runner Disabled Garbage Collection
Test Runner Triggered Garbage Collection
Test Suite Started (%s%ephpunit.xml, 1 test)
Test Suite Started (default, 1 test)
Test Suite Started (PHPUnit\TestFixture\Retry\ControlledGarbageCollection\PassesOnThirdAttemptTest, 1 test)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\ControlledGarbageCollection\PassesOnThirdAttemptTest::testOne, up to 3 attempts)
Test Attempt Failed (PHPUnit\TestFixture\Retry\ControlledGarbageCollection\PassesOnThirdAttemptTest::testOne)
Failure on attempt 1
Test Attempt Failed (PHPUnit\TestFixture\Retry\ControlledGarbageCollection\PassesOnThirdAttemptTest::testOne (attempt 2 of 3))
Failure on attempt 2
Test Preparation Started (PHPUnit\TestFixture\Retry\ControlledGarbageCollection\PassesOnThirdAttemptTest::testOne (attempt 3 of 3))
Test Prepared (PHPUnit\TestFixture\Retry\ControlledGarbageCollection\PassesOnThirdAttemptTest::testOne (attempt 3 of 3))
Test Passed (PHPUnit\TestFixture\Retry\ControlledGarbageCollection\PassesOnThirdAttemptTest::testOne (attempt 3 of 3))
Test Finished (PHPUnit\TestFixture\Retry\ControlledGarbageCollection\PassesOnThirdAttemptTest::testOne (attempt 3 of 3))
Test Runner Triggered Garbage Collection
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\ControlledGarbageCollection\PassesOnThirdAttemptTest::testOne, up to 3 attempts)
Test Suite Finished (PHPUnit\TestFixture\Retry\ControlledGarbageCollection\PassesOnThirdAttemptTest, 1 test)
Test Suite Finished (default, 1 test)
Test Suite Finished (%s%ephpunit.xml, 1 test)
Test Runner Execution Finished
Test Runner Triggered Garbage Collection
Test Runner Enabled Garbage Collection
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
