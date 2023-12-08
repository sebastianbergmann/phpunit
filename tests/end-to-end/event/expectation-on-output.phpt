--TEST--
The right events are emitted in the right order for a test with an output expectation
--FILE--
<?php declare(strict_types=1);
$traceFile = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--disallow-test-output';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-events-text';
$_SERVER['argv'][] = $traceFile;
$_SERVER['argv'][] = __DIR__ . '/../regression/445/Issue445Test.php';

require __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($traceFile);

unlink($traceFile);
--EXPECTF--
PHPUnit Started (PHPUnit %s using %s)
Test Runner Configured
Test Suite Loaded (3 tests)
Event Facade Sealed
Test Runner Started
Test Suite Sorted
Test Runner Execution Started (3 tests)
Test Suite Started (PHPUnit\TestFixture\Issue445Test, 3 tests)
Test Preparation Started (PHPUnit\TestFixture\Issue445Test::testOutputWithExpectationBefore)
Test Prepared (PHPUnit\TestFixture\Issue445Test::testOutputWithExpectationBefore)
Test Passed (PHPUnit\TestFixture\Issue445Test::testOutputWithExpectationBefore)
Test Finished (PHPUnit\TestFixture\Issue445Test::testOutputWithExpectationBefore)
Test Preparation Started (PHPUnit\TestFixture\Issue445Test::testOutputWithExpectationAfter)
Test Prepared (PHPUnit\TestFixture\Issue445Test::testOutputWithExpectationAfter)
Test Passed (PHPUnit\TestFixture\Issue445Test::testOutputWithExpectationAfter)
Test Finished (PHPUnit\TestFixture\Issue445Test::testOutputWithExpectationAfter)
Test Preparation Started (PHPUnit\TestFixture\Issue445Test::testNotMatchingOutput)
Test Prepared (PHPUnit\TestFixture\Issue445Test::testNotMatchingOutput)
Test Failed (PHPUnit\TestFixture\Issue445Test::testNotMatchingOutput)
Failed asserting that two strings are identical.
Test Finished (PHPUnit\TestFixture\Issue445Test::testNotMatchingOutput)
Test Suite Finished (PHPUnit\TestFixture\Issue445Test, 3 tests)
Test Runner Execution Finished
Test Runner Finished
PHPUnit Finished (Shell Exit Code: 1)
