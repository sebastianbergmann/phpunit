--TEST--
The right events are emitted in the right order for a test that runs code which triggers a suppressed E_USER_WARNING
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/SuppressedUserWarningTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\SuppressedUserWarningTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarning)
Test Prepared (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarning)
Test Triggered Warning (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarning, suppressed using operator) in %s:%d
message
Test Passed (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarning)
Test Finished (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarning)
Test Preparation Started (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarningErrorGetLast)
Test Prepared (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarningErrorGetLast)
Test Triggered Warning (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarningErrorGetLast, suppressed using operator) in %s:%d
message
Test Passed (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarningErrorGetLast)
Test Finished (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarningErrorGetLast)
Test Suite Finished (PHPUnit\TestFixture\Event\SuppressedUserWarningTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
