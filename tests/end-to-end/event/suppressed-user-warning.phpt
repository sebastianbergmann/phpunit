--TEST--
The right events are emitted in the right order for a test that runs code which triggers a suppressed E_USER_WARNING
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/_files/SuppressedUserWarningTest.php';

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
Test Suite Started (PHPUnit\TestFixture\Event\SuppressedUserWarningTest, 2 tests)
Test Preparation Started (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarning)
Test Prepared (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarning)
Assertion Succeeded (Constraint: is true, Value: true)
Test Triggered Suppressed Warning (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarning)
message
Test Passed (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarning)
Test Finished (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarning)
Test Preparation Started (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarningErrorGetLast)
Test Prepared (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarningErrorGetLast)
Assertion Succeeded (Constraint: is null, Value: null)
Test Triggered Suppressed Warning (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarningErrorGetLast)
message
Assertion Succeeded (Constraint: is of type array, Value: Array &0 [
    'type' => 512,
    'message' => 'message',
    'file' => '%s%e_files%eSuppressedUserWarningTest.php',
    'line' => %d,
])
Test Passed (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarningErrorGetLast)
Test Finished (PHPUnit\TestFixture\Event\SuppressedUserWarningTest::testSuppressedUserWarningErrorGetLast)
Test Suite Finished (PHPUnit\TestFixture\Event\SuppressedUserWarningTest, 2 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 0)
