--TEST--
Ordering tests by duration without test run history recording triggers a test runner warning
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--order-by=duration-ascending';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/OrderByWarningTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Runner Triggered PHPUnit Warning (Tests cannot be ordered by duration because recording of the test run history is disabled)
Event Facade Sealed
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\TestRunHistory\OrderByWarningTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\TestRunHistory\OrderByWarningTest::testOne)
Test Prepared (PHPUnit\TestFixture\TestRunHistory\OrderByWarningTest::testOne)
Test Passed (PHPUnit\TestFixture\TestRunHistory\OrderByWarningTest::testOne)
Test Finished (PHPUnit\TestFixture\TestRunHistory\OrderByWarningTest::testOne)
Test Suite Finished (PHPUnit\TestFixture\TestRunHistory\OrderByWarningTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
