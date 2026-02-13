--TEST--
E_USER_ERROR emits Test Triggered Error event and aborts test with ErrorException
--SKIPIF--
<?php declare(strict_types=1);
if (version_compare(PHP_VERSION, '8.4.0-dev', '>=')) {
    print 'skip: PHP < 8.4 is required.';
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
Test Triggered Error (PHPUnit\TestFixture\ErrorHandler\UserErrorTest::testUserError) in %s:%d
error message
Test Errored (PHPUnit\TestFixture\ErrorHandler\UserErrorTest::testUserError)
E_USER_ERROR was triggered
Test Finished (PHPUnit\TestFixture\ErrorHandler\UserErrorTest::testUserError)
Test Suite Finished (PHPUnit\TestFixture\ErrorHandler\UserErrorTest, 1 test)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
