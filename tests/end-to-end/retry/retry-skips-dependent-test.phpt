--TEST--
Test that depends on a retried test is skipped when all attempts of the dependency fail
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DependsOnFailingRetriedTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Retry\DependsOnFailingRetriedTest, 2 tests)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\DependsOnFailingRetriedTest::testOne, up to 2 attempts)
Test Attempt Failed (PHPUnit\TestFixture\Retry\DependsOnFailingRetriedTest::testOne)
Failure on attempt 1
Test Preparation Started (PHPUnit\TestFixture\Retry\DependsOnFailingRetriedTest::testOne (attempt 2 of 2))
Test Prepared (PHPUnit\TestFixture\Retry\DependsOnFailingRetriedTest::testOne (attempt 2 of 2))
Test Failed (PHPUnit\TestFixture\Retry\DependsOnFailingRetriedTest::testOne (attempt 2 of 2))
Failure on attempt 2
Test Finished (PHPUnit\TestFixture\Retry\DependsOnFailingRetriedTest::testOne (attempt 2 of 2))
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\DependsOnFailingRetriedTest::testOne, up to 2 attempts)
Test Skipped (PHPUnit\TestFixture\Retry\DependsOnFailingRetriedTest::testTwo)
This test depends on "PHPUnit\TestFixture\Retry\DependsOnFailingRetriedTest::testOne" to pass
Test Suite Finished (PHPUnit\TestFixture\Retry\DependsOnFailingRetriedTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
