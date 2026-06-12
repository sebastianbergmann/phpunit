--TEST--
#[Retry] retries a test whose tearDown() method errors after the test method passed
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/ErrorInTearDownTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Retry\ErrorInTearDownTest, 1 test)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\ErrorInTearDownTest::testOne, up to 2 attempts)
Test Attempt Errored (PHPUnit\TestFixture\Retry\ErrorInTearDownTest::testOne)
Error in tearDown() on first attempt
Test Preparation Started (PHPUnit\TestFixture\Retry\ErrorInTearDownTest::testOne (attempt 2 of 2))
Test Prepared (PHPUnit\TestFixture\Retry\ErrorInTearDownTest::testOne (attempt 2 of 2))
After Test Method Called (PHPUnit\TestFixture\Retry\ErrorInTearDownTest::tearDown)
After Test Method Finished:
- PHPUnit\TestFixture\Retry\ErrorInTearDownTest::tearDown
Test Passed (PHPUnit\TestFixture\Retry\ErrorInTearDownTest::testOne (attempt 2 of 2))
Test Finished (PHPUnit\TestFixture\Retry\ErrorInTearDownTest::testOne (attempt 2 of 2))
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\ErrorInTearDownTest::testOne, up to 2 attempts)
Test Suite Finished (PHPUnit\TestFixture\Retry\ErrorInTearDownTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
