--TEST--
#[Retry] works with --filter to select specific tests
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--filter';
$_SERVER['argv'][] = 'testOne';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/SuccessTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Event Facade Sealed
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Suite Filtered (1 test)
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\Retry\SuccessTest, 1 test)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\SuccessTest::testOne, up to 2 attempts)
Test Preparation Started (PHPUnit\TestFixture\Retry\SuccessTest::testOne)
Test Prepared (PHPUnit\TestFixture\Retry\SuccessTest::testOne)
Test Passed (PHPUnit\TestFixture\Retry\SuccessTest::testOne)
Test Finished (PHPUnit\TestFixture\Retry\SuccessTest::testOne)
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\SuccessTest::testOne, up to 2 attempts)
Test Suite Finished (PHPUnit\TestFixture\Retry\SuccessTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
