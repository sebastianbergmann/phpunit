--TEST--
The right events are emitted in the right order for a test that calls any()
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/AnyInvokedCountTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\AnyInvokedCountTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\AnyInvokedCountTest::testAny)
Test Prepared (PHPUnit\TestFixture\Event\AnyInvokedCountTest::testAny)
Mock Object Created (PHPUnit\TestFixture\Event\AnyInvokedCountInterface)
Test Triggered PHPUnit Deprecation (PHPUnit\TestFixture\Event\AnyInvokedCountTest::testAny)
The any() invoked count expectation is deprecated and will be removed in PHPUnit 14. Use a test stub instead or configure a real invocation count expectation.
Test Passed (PHPUnit\TestFixture\Event\AnyInvokedCountTest::testAny)
Test Finished (PHPUnit\TestFixture\Event\AnyInvokedCountTest::testAny)
Test Suite Finished (PHPUnit\TestFixture\Event\AnyInvokedCountTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
