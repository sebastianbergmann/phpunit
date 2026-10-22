--TEST--
The right events are emitted in the right order for a test that uses the deprecated id() and after() methods for mock object expectations
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/DeprecatedIdAndAfterForMockObjectExpectationsTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\DeprecatedIdAndAfterForMockObjectExpectationsTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\Event\DeprecatedIdAndAfterForMockObjectExpectationsTest::testIdAndAfter)
Test Prepared (PHPUnit\TestFixture\Event\DeprecatedIdAndAfterForMockObjectExpectationsTest::testIdAndAfter)
Mock Object Created (PHPUnit\TestFixture\Event\DeprecatedIdAndAfterForMockObjectExpectationsInterface)
Test Triggered PHPUnit Deprecation (PHPUnit\TestFixture\Event\DeprecatedIdAndAfterForMockObjectExpectationsTest::testIdAndAfter)
id() for mock object expectations is deprecated and will be removed in PHPUnit 15.
Test Triggered PHPUnit Deprecation (PHPUnit\TestFixture\Event\DeprecatedIdAndAfterForMockObjectExpectationsTest::testIdAndAfter)
after() for mock object expectations is deprecated and will be removed in PHPUnit 15.
Test Passed (PHPUnit\TestFixture\Event\DeprecatedIdAndAfterForMockObjectExpectationsTest::testIdAndAfter)
Test Finished (PHPUnit\TestFixture\Event\DeprecatedIdAndAfterForMockObjectExpectationsTest::testIdAndAfter)
Test Suite Finished (PHPUnit\TestFixture\Event\DeprecatedIdAndAfterForMockObjectExpectationsTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
