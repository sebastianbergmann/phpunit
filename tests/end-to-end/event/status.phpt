--TEST--
The right events are emitted in the right order for tests that have different statuses
--SKIPIF--
<?php declare(strict_types=1);
if (DIRECTORY_SEPARATOR === '\\') {
    print "skip: this test does not work on Windows / GitHub Actions\n";
}
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/../_files/basic/unit/StatusTest.php';

require __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main(false);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
Test Runner Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (15 tests)
Test Suite Sorted
Event Facade Sealed
Test Runner Execution Started (15 tests)
Test Suite Started (PHPUnit\SelfTest\Basic\StatusTest, 15 tests)
Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testSuccess)
Mock Object Created (PHPUnit\TestFixture\MockObject\AnInterface)
Assertion Succeeded (Constraint: is true, Value: true)
Test Passed (PHPUnit\SelfTest\Basic\StatusTest::testSuccess)
Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testSuccess)
Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testFailure)
Assertion Failed (Constraint: is true, Value: false)
Test Failed (PHPUnit\SelfTest\Basic\StatusTest::testFailure)
Failed asserting that false is true.
Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testFailure)
Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testError)
Test Errored (PHPUnit\SelfTest\Basic\StatusTest::testError)
Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testError)
Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testIncomplete)
Test Aborted (PHPUnit\SelfTest\Basic\StatusTest::testIncomplete)
Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testIncomplete)
Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testSkipped)
Test Skipped (PHPUnit\SelfTest\Basic\StatusTest::testSkipped)
Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testSkipped)
Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testRisky)
Test Passed (PHPUnit\SelfTest\Basic\StatusTest::testRisky)
Test Considered Risky (PHPUnit\SelfTest\Basic\StatusTest::testRisky)
This test did not perform any assertions
Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testRisky)
Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testWarning)
Test Passed With Warning (PHPUnit\SelfTest\Basic\StatusTest::testWarning)
Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testWarning)
Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testSuccessWithMessage)
Assertion Succeeded (Constraint: is true, Value: true, Message: success with custom message)
Test Passed (PHPUnit\SelfTest\Basic\StatusTest::testSuccessWithMessage)
Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testSuccessWithMessage)
Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testFailureWithMessage)
Assertion Failed (Constraint: is true, Value: false, Message: failure with custom message)
Test Failed (PHPUnit\SelfTest\Basic\StatusTest::testFailureWithMessage)
failure with custom message
Failed asserting that false is true.
Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testFailureWithMessage)
Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testErrorWithMessage)
Test Errored (PHPUnit\SelfTest\Basic\StatusTest::testErrorWithMessage)
error with custom message
Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testErrorWithMessage)
Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testIncompleteWithMessage)
Test Aborted (PHPUnit\SelfTest\Basic\StatusTest::testIncompleteWithMessage)
incomplete with custom message
Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testIncompleteWithMessage)
Test Skipped (PHPUnit\SelfTest\Basic\StatusTest::testSkippedByMetadata)
PHP > 9000 is required.
Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testSkippedWithMessage)
Test Skipped (PHPUnit\SelfTest\Basic\StatusTest::testSkippedWithMessage)
skipped with custom message
Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testSkippedWithMessage)
Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testRiskyWithMessage)
Test Passed (PHPUnit\SelfTest\Basic\StatusTest::testRiskyWithMessage)
Test Considered Risky (PHPUnit\SelfTest\Basic\StatusTest::testRiskyWithMessage)
This test did not perform any assertions
Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testRiskyWithMessage)
Test Prepared (PHPUnit\SelfTest\Basic\StatusTest::testWarningWithMessage)
Test Passed With Warning (PHPUnit\SelfTest\Basic\StatusTest::testWarningWithMessage)
warning with custom message
Test Finished (PHPUnit\SelfTest\Basic\StatusTest::testWarningWithMessage)
Test Suite Finished (PHPUnit\SelfTest\Basic\StatusTest, 15 tests)
Test Runner Execution Finished
Test Runner Finished
