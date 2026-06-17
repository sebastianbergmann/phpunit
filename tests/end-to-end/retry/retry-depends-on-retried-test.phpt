--TEST--
Test that depends on a retried test runs when a retry succeeds
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DependsOnRetriedTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Retry\DependsOnRetriedTest, 2 tests)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\DependsOnRetriedTest::testOne, up to 2 attempts)
Test Attempt Failed (PHPUnit\TestFixture\Retry\DependsOnRetriedTest::testOne)
Failure on first attempt
Test Preparation Started (PHPUnit\TestFixture\Retry\DependsOnRetriedTest::testOne (attempt 2 of 2))
Test Prepared (PHPUnit\TestFixture\Retry\DependsOnRetriedTest::testOne (attempt 2 of 2))
Test Passed (PHPUnit\TestFixture\Retry\DependsOnRetriedTest::testOne (attempt 2 of 2))
Test Finished (PHPUnit\TestFixture\Retry\DependsOnRetriedTest::testOne (attempt 2 of 2))
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\DependsOnRetriedTest::testOne, up to 2 attempts)
Test Preparation Started (PHPUnit\TestFixture\Retry\DependsOnRetriedTest::testTwo)
Test Prepared (PHPUnit\TestFixture\Retry\DependsOnRetriedTest::testTwo)
Test Passed (PHPUnit\TestFixture\Retry\DependsOnRetriedTest::testTwo)
Test Finished (PHPUnit\TestFixture\Retry\DependsOnRetriedTest::testTwo)
Test Suite Finished (PHPUnit\TestFixture\Retry\DependsOnRetriedTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
