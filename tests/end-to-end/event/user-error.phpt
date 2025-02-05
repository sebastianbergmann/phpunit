--TEST--
The right events are emitted in the right order for a test that runs code which triggers E_USER_ERROR
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
Test Suite Loaded (2 tests)
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Event\UserErrorTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\UserErrorTest::testUserError)
Test Prepared (PHPUnit\TestFixture\Event\UserErrorTest::testUserError)
Test Triggered Error (PHPUnit\TestFixture\Event\UserErrorTest::testUserError) in %s:%d
message
Test Errored (PHPUnit\TestFixture\Event\UserErrorTest::testUserError)
E_USER_ERROR was triggered
Test Finished (PHPUnit\TestFixture\Event\UserErrorTest::testUserError)
Test Preparation Started (PHPUnit\TestFixture\Event\UserErrorTest::testUserErrorMustAbortExecution)
Test Prepared (PHPUnit\TestFixture\Event\UserErrorTest::testUserErrorMustAbortExecution)
Test Triggered Error (PHPUnit\TestFixture\Event\UserErrorTest::testUserErrorMustAbortExecution) in %s:%d
message
Test Errored (PHPUnit\TestFixture\Event\UserErrorTest::testUserErrorMustAbortExecution)
E_USER_ERROR was triggered
Test Finished (PHPUnit\TestFixture\Event\UserErrorTest::testUserErrorMustAbortExecution)
Test Suite Finished (PHPUnit\TestFixture\Event\UserErrorTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
