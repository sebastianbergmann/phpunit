--TEST--
phpunit --testdox-xml php://stdout ../../_files/StatusTest.php
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '--no-configuration',
    '--testdox-xml',
    'php://stdout',
    \realpath(__DIR__ . '/../../basic/unit/StatusTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.FEISRW.FEISRW                                                    14 / 14 (100%)<?xml version="1.0" encoding="UTF-8"?>
<tests>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testSuccess" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Success" status="0" time="%s" size="-1" groups="default">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
    <testDouble type="AnInterface"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testFailure" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Failure" status="3" time="%s" size="-1" groups="default" exceptionLine="%d" exceptionMessage="Failed asserting that false is true.">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testError" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Error" status="4" time="%s" size="-1" groups="default" exceptionMessage="">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testIncomplete" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Incomplete" status="2" time="%s" size="-1" groups="default">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testSkipped" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Skipped" status="1" time="%s" size="-1" groups="default">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testRisky" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Risky" status="5" time="%s" size="-1" groups="default">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testWarning" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Warning" status="6" time="%s" size="-1" groups="default">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testSuccessWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Success with message" status="0" time="%s" size="-1" groups="default">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testFailureWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Failure with message" status="3" time="%s" size="-1" groups="default" exceptionLine="%d" exceptionMessage="failure with custom message&#10;Failed asserting that false is true.">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testErrorWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Error with message" status="4" time="%s" size="-1" groups="default" exceptionMessage="error with custom message">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testIncompleteWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Incomplete with message" status="2" time="%s" size="-1" groups="default">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testSkippedWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Skipped with message" status="1" time="%s" size="-1" groups="default">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testRiskyWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Risky with message" status="5" time="%s" size="-1" groups="default">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testWarningWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Warning with message" status="6" time="%s" size="-1" groups="default">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
</tests>


Time: %s, Memory: %s

There were 2 errors:

1) PHPUnit\SelfTest\Basic\StatusTest::testError
RuntimeException:%w

%s%eStatusTest.php:%d

2) PHPUnit\SelfTest\Basic\StatusTest::testErrorWithMessage
RuntimeException: error with custom message

%s%eStatusTest.php:%d

--

There were 2 warnings:

1) PHPUnit\SelfTest\Basic\StatusTest::testWarning

%s%eStatusTest.php:%d

2) PHPUnit\SelfTest\Basic\StatusTest::testWarningWithMessage
warning with custom message

%s%eStatusTest.php:%d

--

There were 2 failures:

1) PHPUnit\SelfTest\Basic\StatusTest::testFailure
Failed asserting that false is true.

%s%eStatusTest.php:%d

2) PHPUnit\SelfTest\Basic\StatusTest::testFailureWithMessage
failure with custom message
Failed asserting that false is true.

%s%eStatusTest.php:%d

--

There were 2 risky tests:

1) PHPUnit\SelfTest\Basic\StatusTest::testRisky
This test did not perform any assertions

%s%eStatusTest.php:%d

2) PHPUnit\SelfTest\Basic\StatusTest::testRiskyWithMessage
This test did not perform any assertions

%s%eStatusTest.php:%d

ERRORS!
Tests: 14, Assertions: 4, Errors: 2, Failures: 2, Warnings: 2, Skipped: 2, Incomplete: 2, Risky: 2.
