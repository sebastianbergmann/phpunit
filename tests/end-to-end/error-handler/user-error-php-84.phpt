--TEST--
E_USER_ERROR on PHP >= 8.4 emits PHP Deprecation then Error event
--SKIPIF--
<?php declare(strict_types=1);
if (version_compare('8.4.0', PHP_VERSION, '>')) {
    print 'skip: PHP >= 8.4 is required.';
}
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = __DIR__ . '/_files/UserErrorTest.php';

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
Test Suite Started (PHPUnit\TestFixture\ErrorHandler\UserErrorTest, 1 test)
Test Preparation Started (PHPUnit\TestFixture\ErrorHandler\UserErrorTest::testUserError)
Test Prepared (PHPUnit\TestFixture\ErrorHandler\UserErrorTest::testUserError)
Test Triggered PHP Deprecation (PHPUnit\TestFixture\ErrorHandler\UserErrorTest::testUserError, unknown if issue was triggered in first-party code or third-party code) in %s:%d
Passing E_USER_ERROR to trigger_error() is deprecated since 8.4, throw an exception or call exit with a string message instead
Test Triggered Error (PHPUnit\TestFixture\ErrorHandler\UserErrorTest::testUserError) in %s:%d
error message
Test Errored (PHPUnit\TestFixture\ErrorHandler\UserErrorTest::testUserError)
E_USER_ERROR was triggered
Test Finished (PHPUnit\TestFixture\ErrorHandler\UserErrorTest::testUserError)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\UserErrorTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
