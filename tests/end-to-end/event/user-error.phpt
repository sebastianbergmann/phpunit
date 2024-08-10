--TEST--
The right events are emitted in the right order for a test that runs code which triggers E_USER_ERROR
--SKIPIF--
<?php declare(strict_types=1);
if (!version_compare('8.4.0-dev', PHP_VERSION)) {
    print 'skip: PHP < 8.4 is required.';
}
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/_files/UserErrorTest.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (2 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (2 tests)
Test Suite Started (PHPUnit\TestFixture\Event\UserErrorTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\UserErrorTest::testUserError)
Test Prepared (PHPUnit\TestFixture\Event\UserErrorTest::testUserError)
Test Triggered Error (PHPUnit\TestFixture\Event\UserErrorTest::testUserError)
message
Test Errored (PHPUnit\TestFixture\Event\UserErrorTest::testUserError)
E_USER_ERROR was triggered
Test Finished (PHPUnit\TestFixture\Event\UserErrorTest::testUserError)
Test Preparation Started (PHPUnit\TestFixture\Event\UserErrorTest::testUserErrorMustAbortExecution)
Test Prepared (PHPUnit\TestFixture\Event\UserErrorTest::testUserErrorMustAbortExecution)
Test Triggered Error (PHPUnit\TestFixture\Event\UserErrorTest::testUserErrorMustAbortExecution)
message
Test Errored (PHPUnit\TestFixture\Event\UserErrorTest::testUserErrorMustAbortExecution)
E_USER_ERROR was triggered
Test Finished (PHPUnit\TestFixture\Event\UserErrorTest::testUserErrorMustAbortExecution)
Test Suite Finished (PHPUnit\TestFixture\Event\UserErrorTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 2)
