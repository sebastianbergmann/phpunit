--TEST--
phpunit --debug -c tests/basic/configuration.basic.xml
--FILE--
<?php declare(strict_types=1);
require_once(__DIR__ . '/../../bootstrap.php');

$arguments = [
    '-c',
    \realpath(__DIR__ . '/../../basic/configuration.basic.xml'),
    '--debug',
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test 'vendor\project\StatusTest::testSuccess' started
Test 'vendor\project\StatusTest::testSuccess' ended
Test 'vendor\project\StatusTest::testFailure' started
Test 'vendor\project\StatusTest::testFailure' ended
Test 'vendor\project\StatusTest::testError' started
Test 'vendor\project\StatusTest::testError' ended
Test 'vendor\project\StatusTest::testIncomplete' started
Test 'vendor\project\StatusTest::testIncomplete' ended
Test 'vendor\project\StatusTest::testSkipped' started
Test 'vendor\project\StatusTest::testSkipped' ended
Test 'vendor\project\StatusTest::testRisky' started
Test 'vendor\project\StatusTest::testRisky' ended
Test 'vendor\project\StatusTest::testWarning' started
Test 'vendor\project\StatusTest::testWarning' ended
Test 'vendor\project\StatusTest::testSuccessWithMessage' started
Test 'vendor\project\StatusTest::testSuccessWithMessage' ended
Test 'vendor\project\StatusTest::testFailureWithMessage' started
Test 'vendor\project\StatusTest::testFailureWithMessage' ended
Test 'vendor\project\StatusTest::testErrorWithMessage' started
Test 'vendor\project\StatusTest::testErrorWithMessage' ended
Test 'vendor\project\StatusTest::testIncompleteWithMessage' started
Test 'vendor\project\StatusTest::testIncompleteWithMessage' ended
Test 'vendor\project\StatusTest::testSkippedWithMessage' started
Test 'vendor\project\StatusTest::testSkippedWithMessage' ended
Test 'vendor\project\StatusTest::testRiskyWithMessage' started
Test 'vendor\project\StatusTest::testRiskyWithMessage' ended
Test 'vendor\project\StatusTest::testWarningWithMessage' started
Test 'vendor\project\StatusTest::testWarningWithMessage' ended


Time: %s, Memory: %s

There were 2 errors:

1) vendor\project\StatusTest::testError
RuntimeException:%w

%stests%ebasic%eunit%eStatusTest.php:%d

2) vendor\project\StatusTest::testErrorWithMessage
RuntimeException: error with custom message

%stests%ebasic%eunit%eStatusTest.php:%d

--

There were 2 warnings:

1) vendor\project\StatusTest::testWarning

%stests%ebasic%eunit%eStatusTest.php:%d

2) vendor\project\StatusTest::testWarningWithMessage
warning with custom message

%stests%ebasic%eunit%eStatusTest.php:%d

--

There were 2 failures:

1) vendor\project\StatusTest::testFailure
Failed asserting that false is true.

%stests%ebasic%eunit%eStatusTest.php:%d

2) vendor\project\StatusTest::testFailureWithMessage
failure with custom message
Failed asserting that false is true.

%stests%ebasic%eunit%eStatusTest.php:%d

--

There were 2 risky tests:

1) vendor\project\StatusTest::testRisky
This test did not perform any assertions

%stests%ebasic%eunit%eStatusTest.php:%d

2) vendor\project\StatusTest::testRiskyWithMessage
This test did not perform any assertions

%stests%ebasic%eunit%eStatusTest.php:%d

ERRORS!
Tests: 14, Assertions: 4, Errors: 2, Failures: 2, Warnings: 2, Skipped: 2, Incomplete: 2, Risky: 2.
