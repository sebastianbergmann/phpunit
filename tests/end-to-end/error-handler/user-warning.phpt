--TEST--
E_USER_WARNING triggered by trigger_error() emits Test Triggered Warning event
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
Test Suite Loaded (1 test)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (1 test)
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\UserWarningTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\UserWarningTest::testUserWarning)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\UserWarningTest::testUserWarning)
Test Triggered Warning (PHPUnit\TestFixture\ErrorHandler\UserWarningTest::testUserWarning) in %s:%d
warning message
Test Passed (PHPUnit\TestFixture\ErrorHandler\UserWarningTest::testUserWarning)
Test Finished (PHPUnit\TestFixture\ErrorHandler\UserWarningTest::testUserWarning)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\UserWarningTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
