--TEST--
#[Retry] works with --order-by=defects
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--order-by=defects';
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
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Retry\SuccessTest, 2 tests)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\SuccessTest::testOne, up to 2 attempts)
Test Preparation Started (PHPUnit\TestFixture\Retry\SuccessTest::testOne)
Test Prepared (PHPUnit\TestFixture\Retry\SuccessTest::testOne)
Test Passed (PHPUnit\TestFixture\Retry\SuccessTest::testOne)
Test Finished (PHPUnit\TestFixture\Retry\SuccessTest::testOne)
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\SuccessTest::testOne, up to 2 attempts)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\SuccessTest::testTwo, up to 2 attempts)
Test Preparation Started (PHPUnit\TestFixture\Retry\SuccessTest::testTwo)
Test Prepared (PHPUnit\TestFixture\Retry\SuccessTest::testTwo)
Test Passed (PHPUnit\TestFixture\Retry\SuccessTest::testTwo)
Test Finished (PHPUnit\TestFixture\Retry\SuccessTest::testTwo)
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\SuccessTest::testTwo, up to 2 attempts)
Test Suite Finished (PHPUnit\TestFixture\Retry\SuccessTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
