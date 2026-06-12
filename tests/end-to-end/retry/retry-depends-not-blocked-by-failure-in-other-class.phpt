--TEST--
#[Retry] does not block dependent test when a method with the same name exhausts all attempts in another class
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DependsBlockedByOtherClassFailingTest.php';
$_SERVER['argv'][] = __DIR__ . '/_files/DependsBlockedByOtherClassDependingTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (3 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (CLI Arguments, 3 tests)
Test Suite Started (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassFailingTest, 1 test)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassFailingTest::testSomething, up to 2 attempts)
Test Attempt Failed (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassFailingTest::testSomething)
Failure in unrelated class
Test Preparation Started (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassFailingTest::testSomething (attempt 2 of 2))
Test Prepared (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassFailingTest::testSomething (attempt 2 of 2))
Test Failed (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassFailingTest::testSomething (attempt 2 of 2))
Failure in unrelated class
Test Finished (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassFailingTest::testSomething (attempt 2 of 2))
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassFailingTest::testSomething, up to 2 attempts)
Test Suite Finished (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassFailingTest, 1 test)
Test Suite Started (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassDependingTest, 2 tests)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassDependingTest::testSomething, up to 2 attempts)
Test Preparation Started (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassDependingTest::testSomething)
Test Prepared (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassDependingTest::testSomething)
Test Passed (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassDependingTest::testSomething)
Test Finished (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassDependingTest::testSomething)
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassDependingTest::testSomething, up to 2 attempts)
Test Preparation Started (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassDependingTest::testDependent)
Test Prepared (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassDependingTest::testDependent)
Test Passed (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassDependingTest::testDependent)
Test Finished (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassDependingTest::testDependent)
Test Suite Finished (PHPUnit\TestFixture\Retry\DependsBlockedByOtherClassDependingTest, 2 tests)
Test Suite Finished (CLI Arguments, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
