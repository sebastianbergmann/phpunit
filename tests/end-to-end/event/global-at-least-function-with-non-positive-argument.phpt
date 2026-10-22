--TEST--
The right events are emitted in the right order for a test that calls the global atLeast() function with a non-positive argument
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/GlobalAtLeastFunctionWithNonPositiveArgumentTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\GlobalAtLeastFunctionWithNonPositiveArgumentTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\GlobalAtLeastFunctionWithNonPositiveArgumentTest::testAtLeastWithZero)
Test Prepared (PHPUnit\TestFixture\Event\GlobalAtLeastFunctionWithNonPositiveArgumentTest::testAtLeastWithZero)
Mock Object Created (PHPUnit\TestFixture\Event\GlobalAtLeastFunctionWithNonPositiveArgumentInterface)
Test Triggered PHPUnit Deprecation (PHPUnit\TestFixture\Event\GlobalAtLeastFunctionWithNonPositiveArgumentTest::testAtLeastWithZero)
Calling atLeast() with an argument that is not positive is deprecated.
This will become an error in PHPUnit 14.
Test Passed (PHPUnit\TestFixture\Event\GlobalAtLeastFunctionWithNonPositiveArgumentTest::testAtLeastWithZero)
Test Finished (PHPUnit\TestFixture\Event\GlobalAtLeastFunctionWithNonPositiveArgumentTest::testAtLeastWithZero)
Test Suite Finished (PHPUnit\TestFixture\Event\GlobalAtLeastFunctionWithNonPositiveArgumentTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
