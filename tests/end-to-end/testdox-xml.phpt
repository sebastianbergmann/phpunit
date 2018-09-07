--TEST--
phpunit --testdox-xml php://stdout StatusTest ../../_files/StatusTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--testdox-xml';
$_SERVER['argv'][3] = 'php://stdout';
$_SERVER['argv'][4] = 'StatusTest';
$_SERVER['argv'][5] = __DIR__ . '/../_files/StatusTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.FEISRW                                                             7 / 7 (100%)<?xml version="1.0" encoding="UTF-8"?>
<tests>
  <test className="vendor\project\StatusTest" methodName="testSuccess" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Success" status="0" time="%s" size="-1" groups="default"/>
  <test className="vendor\project\StatusTest" methodName="testFailure" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Failure" status="3" time="%s" size="-1" groups="default" exceptionLine="16" exceptionMessage="Failed asserting that false is true."/>
  <test className="vendor\project\StatusTest" methodName="testError" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Error" status="4" time="%s" size="-1" groups="default" exceptionMessage=""/>
  <test className="vendor\project\StatusTest" methodName="testIncomplete" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Incomplete" status="2" time="%s" size="-1" groups="default"/>
  <test className="vendor\project\StatusTest" methodName="testSkipped" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Skipped" status="1" time="%s" size="-1" groups="default"/>
  <test className="vendor\project\StatusTest" methodName="testRisky" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Risky" status="5" time="%s" size="-1" groups="default"/>
  <test className="vendor\project\StatusTest" methodName="testWarning" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Warning" status="6" time="%s" size="-1" groups="default"/>
</tests>


Time: %s, Memory: %s

There was 1 error:

1) vendor\project\StatusTest::testError
RuntimeException:%s

%s%eStatusTest.php:%d

--

There was 1 warning:

1) vendor\project\StatusTest::testWarning

%s%eStatusTest.php:%d

--

There was 1 failure:

1) vendor\project\StatusTest::testFailure
Failed asserting that false is true.

%s%eStatusTest.php:%d

--

There was 1 risky test:

1) vendor\project\StatusTest::testRisky
This test did not perform any assertions

ERRORS!
Tests: 7, Assertions: 2, Errors: 1, Failures: 1, Warnings: 1, Skipped: 1, Incomplete: 1, Risky: 1.
