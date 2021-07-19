--TEST--
The right events are emitted in the right order for tests that have different statuses
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--trace-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/../../basic/unit/StatusTest.php';

require __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main(false);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
%a
[%s] [%s] Test Runner Started (PHPUnit %s using %s)
[%s] [%s] Test Runner Configuration Combined
[%s] [%s] Test Suite Loaded (14 tests)
[%s] [%s] Test Suite Sorted
[%s] [%s] Event Facade Sealed
[%s] [%s] Test Suite Started (PHPUnit\SelfTest\Basic\StatusTest)
[%s] [%s] Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testSuccess)
[%s] [%s] Mock Object Created (PHPUnit\TestFixture\AnInterface)
[%s] [%s] Assertion Made (Constraint: is true - Value: true - Failed: false - Message: )
[%s] [%s] Test Passed (PHPUnit\SelfTest\Basic\StatusTest::testSuccess)
[%s] [%s] Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testSuccess)
[%s] [%s] Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testFailure)
[%s] [%s] Assertion Made (Constraint: is true - Value: false - Failed: true - Message: )
[%s] [%s] Test Failed (PHPUnit\SelfTest\Basic\StatusTest::testFailure)
Failed asserting that false is true.
[%s] [%s] Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testFailure)
[%s] [%s] Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testError)
[%s] [%s] Test Errored (PHPUnit\SelfTest\Basic\StatusTest::testError)
[%s] [%s] Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testError)
[%s] [%s] Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testIncomplete)
[%s] [%s] Test Aborted (PHPUnit\SelfTest\Basic\StatusTest::testIncomplete)
[%s] [%s] Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testIncomplete)
[%s] [%s] Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testSkipped)
[%s] [%s] Test Skipped (PHPUnit\SelfTest\Basic\StatusTest::testSkipped)
[%s] [%s] Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testSkipped)
[%s] [%s] Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testRisky)
[%s] [%s] Test Passed But Risky (PHPUnit\SelfTest\Basic\StatusTest::testRisky)
[%s] [%s] Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testRisky)
This test did not perform any assertions

%s/StatusTest.php:53
[%s] [%s] Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testWarning)
[%s] [%s] Test Passed With Warning (PHPUnit\SelfTest\Basic\StatusTest::testWarning)
[%s] [%s] Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testWarning)
[%s] [%s] Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testSuccessWithMessage)
[%s] [%s] Assertion Made (Constraint: is true - Value: true - Failed: false - Message: "success with custom message")
[%s] [%s] Test Passed (PHPUnit\SelfTest\Basic\StatusTest::testSuccessWithMessage)
[%s] [%s] Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testSuccessWithMessage)
[%s] [%s] Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testFailureWithMessage)
[%s] [%s] Assertion Made (Constraint: is true - Value: false - Failed: true - Message: failure with custom message)
[%s] [%s] Test Failed (PHPUnit\SelfTest\Basic\StatusTest::testFailureWithMessage)
failure with custom message
Failed asserting that false is true.
[%s] [%s] Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testFailureWithMessage)
[%s] [%s] Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testErrorWithMessage)
[%s] [%s] Test Errored (PHPUnit\SelfTest\Basic\StatusTest::testErrorWithMessage)
error with custom message
[%s] [%s] Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testErrorWithMessage)
[%s] [%s] Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testIncompleteWithMessage)
[%s] [%s] Test Aborted (PHPUnit\SelfTest\Basic\StatusTest::testIncompleteWithMessage)
incomplete with custom message
[%s] [%s] Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testIncompleteWithMessage)
[%s] [%s] Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testSkippedWithMessage)
[%s] [%s] Test Skipped (PHPUnit\SelfTest\Basic\StatusTest::testSkippedWithMessage)
skipped with custom message
[%s] [%s] Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testSkippedWithMessage)
[%s] [%s] Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testRiskyWithMessage)
[%s] [%s] Test Passed But Risky (PHPUnit\SelfTest\Basic\StatusTest::testRiskyWithMessage)
This test did not perform any assertions

%s/StatusTest.php:87
[%s] [%s] Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testRiskyWithMessage)
[%s] [%s] Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testWarningWithMessage)
[%s] [%s] Test Passed With Warning (PHPUnit\SelfTest\Basic\StatusTest::testWarningWithMessage)
warning with custom message
[%s] [%s] Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testWarningWithMessage)
[%s] [%s] Test Suite Finished (PHPUnit\SelfTest\Basic\StatusTest)
[%s] [%s] Test Runner Finished
