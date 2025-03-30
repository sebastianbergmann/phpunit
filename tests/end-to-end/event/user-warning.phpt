--TEST--
The right events are emitted in the right order for a test that runs code which triggers E_USER_WARNING
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/UserWarningTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\UserWarningTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\UserWarningTest::testUserWarning)
Test Prepared (PHPUnit\TestFixture\Event\UserWarningTest::testUserWarning)
Test Triggered Warning (PHPUnit\TestFixture\Event\UserWarningTest::testUserWarning) in %s:%d
message
Test Passed (PHPUnit\TestFixture\Event\UserWarningTest::testUserWarning)
Test Finished (PHPUnit\TestFixture\Event\UserWarningTest::testUserWarning)
Test Preparation Started (PHPUnit\TestFixture\Event\UserWarningTest::testUserWarningErrorGetLast)
Test Prepared (PHPUnit\TestFixture\Event\UserWarningTest::testUserWarningErrorGetLast)
Test Triggered Warning (PHPUnit\TestFixture\Event\UserWarningTest::testUserWarningErrorGetLast) in %s:%d
message
Test Passed (PHPUnit\TestFixture\Event\UserWarningTest::testUserWarningErrorGetLast)
Test Finished (PHPUnit\TestFixture\Event\UserWarningTest::testUserWarningErrorGetLast)
Test Suite Finished (PHPUnit\TestFixture\Event\UserWarningTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
