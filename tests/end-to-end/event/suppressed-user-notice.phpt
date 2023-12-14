--TEST--
The right events are emitted in the right order for a test that runs code which triggers a suppressed E_USER_NOTICE
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/_files/SuppressedUserNoticeTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNotice)
Test Prepared (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNotice)
Assertion Succeeded (Constraint: is true, Value: true)
Test Triggered Suppressed Notice (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNotice)
message
Test Passed (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNotice)
Test Finished (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNotice)
Test Preparation Started (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNoticeErrorGetLast)
Test Prepared (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNoticeErrorGetLast)
Assertion Succeeded (Constraint: is null, Value: null)
Test Triggered Suppressed Notice (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNoticeErrorGetLast)
message
Assertion Succeeded (Constraint: is of type array, Value: Array &0 [
    'type' => 1024,
    'message' => 'message',
    'file' => '%s%e_files%eSuppressedUserNoticeTest.php',
    'line' => %d,
])
Test Passed (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNoticeErrorGetLast)
Test Finished (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest::testSuppressedUserNoticeErrorGetLast)
Test Suite Finished (PHPUnit\TestFixture\Event\SuppressedUserNoticeTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
