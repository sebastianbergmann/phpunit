--TEST--
#[Retry] takes precedence over the --repeat CLI option
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--repeat';
$_SERVER['argv'][] = '3';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/PassesOnFirstAttemptTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Retry\PassesOnFirstAttemptTest, 1 test)
Test Suite for Retried Test Method Started (PHPUnit\TestFixture\Retry\PassesOnFirstAttemptTest::testOne, up to 3 attempts)
Test Preparation Started (PHPUnit\TestFixture\Retry\PassesOnFirstAttemptTest::testOne)
Test Prepared (PHPUnit\TestFixture\Retry\PassesOnFirstAttemptTest::testOne)
Test Passed (PHPUnit\TestFixture\Retry\PassesOnFirstAttemptTest::testOne)
Test Finished (PHPUnit\TestFixture\Retry\PassesOnFirstAttemptTest::testOne)
Test Suite for Retried Test Method Finished (PHPUnit\TestFixture\Retry\PassesOnFirstAttemptTest::testOne, up to 3 attempts)
Test Suite Finished (PHPUnit\TestFixture\Retry\PassesOnFirstAttemptTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
