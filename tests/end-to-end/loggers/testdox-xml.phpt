--TEST--
phpunit --testdox-xml php://stdout ../../_files/StatusTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox-xml';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../basic/unit/StatusTest.php');

require __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.FEISRW.FEISRW                                                    14 / 14 (100%)<?xml version="1.0" encoding="UTF-8"?>
<tests>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testSuccess" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Success" status="success" time="%s" size="unknown">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
    <testDouble type="PHPUnit\TestFixture\AnInterface"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testFailure" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Failure" status="failure" time="%s" size="unknown" exceptionLine="%d" exceptionMessage="Failed asserting that false is true.">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testError" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Error" status="error" time="%s" size="unknown" exceptionMessage="">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testIncomplete" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Incomplete" status="incomplete" time="%s" size="unknown">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testSkipped" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Skipped" status="skipped" time="%s" size="unknown">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testRisky" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Risky" status="risky" time="%s" size="unknown">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testWarning" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Warning" status="warning" time="%s" size="unknown">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testSuccessWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Success with message" status="success" time="%s" size="unknown">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testFailureWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Failure with message" status="failure" time="%s" size="unknown" exceptionLine="%d" exceptionMessage="failure with custom message&#10;Failed asserting that false is true.">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testErrorWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Error with message" status="error" time="%s" size="unknown" exceptionMessage="error with custom message">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testIncompleteWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Incomplete with message" status="incomplete" time="%s" size="unknown">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testSkippedWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Skipped with message" status="skipped" time="%s" size="unknown">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testRiskyWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Risky with message" status="risky" time="%s" size="unknown">
    <group name="default"/>
    <covers target="Foo"/>
    <uses target="Bar"/>
  </test>
  <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testWarningWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Warning with message" status="warning" time="%s" size="unknown">
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
