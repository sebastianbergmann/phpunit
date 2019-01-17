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


Time: %s, Memory: %s

There were 4 errors:

1) PHPUnit\SelfTest\Basic\SetUpBeforeClassTest::testOne
Exception: forcing an Exception in setUpBeforeClass() in /Users/ewout/proj/phpunit/tests/basic/unit/SetUpBeforeClassTest.php:18
Stack trace:
#0 /Users/ewout/proj/phpunit/src/Framework/TestSuite.php(703): PHPUnit\SelfTest\Basic\SetUpBeforeClassTest::setUpBeforeClass()
#1 /Users/ewout/proj/phpunit/src/Framework/TestSuite.php(746): PHPUnit\Framework\TestSuite->run(Object(PHPUnit\Framework\TestResult))
#2 /Users/ewout/proj/phpunit/src/TextUI/TestRunner.php(642): PHPUnit\Framework\TestSuite->run(Object(PHPUnit\Framework\TestResult))
#3 /Users/ewout/proj/phpunit/src/TextUI/Command.php(207): PHPUnit\TextUI\TestRunner->doRun(Object(PHPUnit\Framework\TestSuite), Array, true)
#4 /Users/ewout/proj/phpunit/src/TextUI/Command.php(163): PHPUnit\TextUI\Command->run(Array, true)
#5 Standard input code(11): PHPUnit\TextUI\Command::main()
#6 {main}
2) PHPUnit\SelfTest\Basic\SetUpBeforeClassTest::testTwo
Exception: forcing an Exception in setUpBeforeClass() in /Users/ewout/proj/phpunit/tests/basic/unit/SetUpBeforeClassTest.php:18
Stack trace:
#0 /Users/ewout/proj/phpunit/src/Framework/TestSuite.php(703): PHPUnit\SelfTest\Basic\SetUpBeforeClassTest::setUpBeforeClass()
#1 /Users/ewout/proj/phpunit/src/Framework/TestSuite.php(746): PHPUnit\Framework\TestSuite->run(Object(PHPUnit\Framework\TestResult))
#2 /Users/ewout/proj/phpunit/src/TextUI/TestRunner.php(642): PHPUnit\Framework\TestSuite->run(Object(PHPUnit\Framework\TestResult))
#3 /Users/ewout/proj/phpunit/src/TextUI/Command.php(207): PHPUnit\TextUI\TestRunner->doRun(Object(PHPUnit\Framework\TestSuite), Array, true)
#4 /Users/ewout/proj/phpunit/src/TextUI/Command.php(163): PHPUnit\TextUI\Command->run(Array, true)
#5 Standard input code(11): PHPUnit\TextUI\Command::main()
#6 {main}
3) PHPUnit\SelfTest\Basic\StatusTest::testError
RuntimeException:%w

%stests%ebasic%eunit%eStatusTest.php:%d

4) PHPUnit\SelfTest\Basic\StatusTest::testErrorWithMessage
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

There were 2 failures:

1) PHPUnit\SelfTest\Basic\StatusTest::testFailure
Failed asserting that false is true.

%stests%ebasic%eunit%eStatusTest.php:%d

2) PHPUnit\SelfTest\Basic\StatusTest::testFailureWithMessage
failure with custom message
Failed asserting that false is true.

%stests%ebasic%eunit%eStatusTest.php:%d

--

There were 2 risky tests:

1) PHPUnit\SelfTest\Basic\StatusTest::testRisky
This test did not perform any assertions

%stests%ebasic%eunit%eStatusTest.php:%d

2) PHPUnit\SelfTest\Basic\StatusTest::testRiskyWithMessage
This test did not perform any assertions

%stests%ebasic%eunit%eStatusTest.php:%d

ERRORS!
Tests: 16, Assertions: 4, Errors: 4, Failures: 2, Warnings: 2, Skipped: 2, Incomplete: 2, Risky: 2.
