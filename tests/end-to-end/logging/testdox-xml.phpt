--TEST--
phpunit --testdox-xml php://stdout ../../_files/StatusTest.php
--FILE--
<?php declare(strict_types=1);
$output = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--testdox-xml';
$_SERVER['argv'][] = $output;
$_SERVER['argv'][] = __DIR__ . '/../_files/basic/unit/StatusTest.php';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main(false);

print file_get_contents($output);

unlink($output);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<tests>
 <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testSuccess" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Success" size="unknown" time="%s" status="success">
  <group name="default"/>
  <covers target="Foo"/>
  <uses target="Bar"/>
  <testDouble type="PHPUnit\TestFixture\MockObject\AnInterface"/>
 </test>
 <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testFailure" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Failure" size="unknown" time="%s" status="failure" exceptionMessage="Failed asserting that false is true.">
  <group name="default"/>
  <covers target="Foo"/>
  <uses target="Bar"/>
 </test>
 <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testError" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Error" size="unknown" time="%s" status="error" exceptionMessage="">
  <group name="default"/>
  <covers target="Foo"/>
  <uses target="Bar"/>
 </test>
 <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testIncomplete" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Incomplete" size="unknown" time="%s" status="incomplete">
  <group name="default"/>
  <covers target="Foo"/>
  <uses target="Bar"/>
 </test>
 <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testSkipped" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Skipped" size="unknown" time="%s" status="skipped">
  <group name="default"/>
  <covers target="Foo"/>
  <uses target="Bar"/>
 </test>
 <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testRisky" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Risky" size="unknown" time="%s" status="risky">
  <group name="default"/>
  <covers target="Foo"/>
  <uses target="Bar"/>
 </test>
 <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testSuccessWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Success with message" size="unknown" time="%s" status="success">
  <group name="default"/>
  <covers target="Foo"/>
  <uses target="Bar"/>
 </test>
 <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testFailureWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Failure with message" size="unknown" time="%s" status="failure" exceptionMessage="failure with custom message&#10;Failed asserting that false is true.">
  <group name="default"/>
  <covers target="Foo"/>
  <uses target="Bar"/>
 </test>
 <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testErrorWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Error with message" size="unknown" time="%s" status="error" exceptionMessage="error with custom message">
  <group name="default"/>
  <covers target="Foo"/>
  <uses target="Bar"/>
 </test>
 <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testIncompleteWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Incomplete with message" size="unknown" time="%s" status="incomplete">
  <group name="default"/>
  <covers target="Foo"/>
  <uses target="Bar"/>
 </test>
 <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testSkippedWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Skipped with message" size="unknown" time="%s" status="skipped">
  <group name="default"/>
  <covers target="Foo"/>
  <uses target="Bar"/>
 </test>
 <test className="PHPUnit\SelfTest\Basic\StatusTest" methodName="testRiskyWithMessage" prettifiedClassName="Test result status with and without message" prettifiedMethodName="Risky with message" size="unknown" time="%s" status="risky">
  <group name="default"/>
  <covers target="Foo"/>
  <uses target="Bar"/>
 </test>
</tests>
