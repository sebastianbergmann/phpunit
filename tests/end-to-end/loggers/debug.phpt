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

Test 'PHPUnit\SelfTest\Basic\SetUpBeforeClassTest::testOne' started
Test 'PHPUnit\SelfTest\Basic\SetUpBeforeClassTest::testOne' ended
Test 'PHPUnit\SelfTest\Basic\SetUpBeforeClassTest::testTwo' started
Test 'PHPUnit\SelfTest\Basic\SetUpBeforeClassTest::testTwo' ended
Test 'PHPUnit\SelfTest\Basic\SetUpTest::testOneWithSetUpException' started
Test 'PHPUnit\SelfTest\Basic\SetUpTest::testOneWithSetUpException' ended
Test 'PHPUnit\SelfTest\Basic\SetUpTest::testTwoWithSetUpException' started
Test 'PHPUnit\SelfTest\Basic\SetUpTest::testTwoWithSetUpException' ended
Test 'PHPUnit\SelfTest\Basic\StatusTest::testSuccess' started
Test 'PHPUnit\SelfTest\Basic\StatusTest::testSuccess' ended
Test 'PHPUnit\SelfTest\Basic\StatusTest::testFailure' started
Test 'PHPUnit\SelfTest\Basic\StatusTest::testFailure' ended
Test 'PHPUnit\SelfTest\Basic\StatusTest::testError' started
Test 'PHPUnit\SelfTest\Basic\StatusTest::testError' ended
Test 'PHPUnit\SelfTest\Basic\StatusTest::testIncomplete' started
Test 'PHPUnit\SelfTest\Basic\StatusTest::testIncomplete' ended
Test 'PHPUnit\SelfTest\Basic\StatusTest::testSkipped' started
Test 'PHPUnit\SelfTest\Basic\StatusTest::testSkipped' ended
Test 'PHPUnit\SelfTest\Basic\StatusTest::testRisky' started
Test 'PHPUnit\SelfTest\Basic\StatusTest::testRisky' ended
Test 'PHPUnit\SelfTest\Basic\StatusTest::testWarning' started
Test 'PHPUnit\SelfTest\Basic\StatusTest::testWarning' ended
Test 'PHPUnit\SelfTest\Basic\StatusTest::testSuccessWithMessage' started
Test 'PHPUnit\SelfTest\Basic\StatusTest::testSuccessWithMessage' ended
Test 'PHPUnit\SelfTest\Basic\StatusTest::testFailureWithMessage' started
Test 'PHPUnit\SelfTest\Basic\StatusTest::testFailureWithMessage' ended
Test 'PHPUnit\SelfTest\Basic\StatusTest::testErrorWithMessage' started
Test 'PHPUnit\SelfTest\Basic\StatusTest::testErrorWithMessage' ended
Test 'PHPUnit\SelfTest\Basic\StatusTest::testIncompleteWithMessage' started
Test 'PHPUnit\SelfTest\Basic\StatusTest::testIncompleteWithMessage' ended
Test 'PHPUnit\SelfTest\Basic\StatusTest::testSkippedWithMessage' started
Test 'PHPUnit\SelfTest\Basic\StatusTest::testSkippedWithMessage' ended
Test 'PHPUnit\SelfTest\Basic\StatusTest::testRiskyWithMessage' started
Test 'PHPUnit\SelfTest\Basic\StatusTest::testRiskyWithMessage' ended
Test 'PHPUnit\SelfTest\Basic\StatusTest::testWarningWithMessage' started
Test 'PHPUnit\SelfTest\Basic\StatusTest::testWarningWithMessage' ended
Test 'PHPUnit\SelfTest\Basic\TearDownAfterClassTest::testOne' started
Test 'PHPUnit\SelfTest\Basic\TearDownAfterClassTest::testOne' ended
Test 'PHPUnit\SelfTest\Basic\TearDownAfterClassTest::testTwo' started
Test 'PHPUnit\SelfTest\Basic\TearDownAfterClassTest::testTwo' ended
Test 'PHPUnit\SelfTest\Basic\TearDownAfterClassTest::tearDownAfterClass' started
Test 'PHPUnit\SelfTest\Basic\TearDownAfterClassTest::tearDownAfterClass' ended


Time: %s, Memory: %s

There were 6 errors:

1) PHPUnit\SelfTest\Basic\SetUpBeforeClassTest::testOne
Exception: forcing an Exception in setUpBeforeClass() in %stests%ebasic%eunit%eSetUpBeforeClassTest.php:%d
Stack trace:
%a
2) PHPUnit\SelfTest\Basic\SetUpBeforeClassTest::testTwo
Exception: forcing an Exception in setUpBeforeClass() in %stests%ebasic%eunit%eSetUpBeforeClassTest.php:%d
Stack trace:
%a
3) PHPUnit\SelfTest\Basic\SetUpTest::testOneWithSetUpException
RuntimeException: throw exception in setUp

%stests%ebasic%eunit%eSetUpTest.php:%d

4) PHPUnit\SelfTest\Basic\SetUpTest::testTwoWithSetUpException
RuntimeException: throw exception in setUp

%stests%ebasic%eunit%eSetUpTest.php:%d

5) PHPUnit\SelfTest\Basic\StatusTest::testError
RuntimeException:%w

%stests%ebasic%eunit%eStatusTest.php:%d

6) PHPUnit\SelfTest\Basic\StatusTest::testErrorWithMessage
RuntimeException: error with custom message

%stests%ebasic%eunit%eStatusTest.php:%d

--

There were 2 warnings:

1) PHPUnit\SelfTest\Basic\StatusTest::testWarning

%stests%ebasic%eunit%eStatusTest.php:%d

2) PHPUnit\SelfTest\Basic\StatusTest::testWarningWithMessage
warning with custom message

%stests%ebasic%eunit%eStatusTest.php:%d

--

There were 3 failures:

1) PHPUnit\SelfTest\Basic\StatusTest::testFailure
Failed asserting that false is true.

%stests%ebasic%eunit%eStatusTest.php:%d

2) PHPUnit\SelfTest\Basic\StatusTest::testFailureWithMessage
failure with custom message
Failed asserting that false is true.

%stests%ebasic%eunit%eStatusTest.php:%d

3) PHPUnit\SelfTest\Basic\TearDownAfterClassTest::tearDownAfterClass
Exception in PHPUnit\SelfTest\Basic\TearDownAfterClassTest::tearDownAfterClass
forcing an Exception in tearDownAfterClass()

%stests%ebasic%eunit%eTearDownAfterClassTest.php:%d

--

There were 2 risky tests:

1) PHPUnit\SelfTest\Basic\StatusTest::testRisky
This test did not perform any assertions

%stests%ebasic%eunit%eStatusTest.php:%d

2) PHPUnit\SelfTest\Basic\StatusTest::testRiskyWithMessage
This test did not perform any assertions

%stests%ebasic%eunit%eStatusTest.php:%d

ERRORS!
Tests: 21, Assertions: 7, Errors: 6, Failures: 3, Warnings: 2, Skipped: 2, Incomplete: 2, Risky: 2.
