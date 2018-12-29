--TEST--
phpunit --testdox-xml php://stdout StatusTest ../../_files/StatusTest.php
--FILE--
<?php
$arguments = [
    '--no-configuration',
    '--testdox-xml',
    'php://stdout',
    'StatusTest',
    \realpath(__DIR__ . '/../../basic/unit/StatusTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.FEISRW.FEISRW                                                    14 / 14 (100%)<?xml version="1.0" encoding="UTF-8"?>
<tests>
  <test className="vendor\project\StatusTest" methodName="testSuccess" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Success" status="0" time="%s" size="-1" groups="default"/>
  <test className="vendor\project\StatusTest" methodName="testFailure" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Failure" status="3" time="%s" size="-1" groups="default" exceptionLine="%d" exceptionMessage="Failed asserting that false is true."/>
  <test className="vendor\project\StatusTest" methodName="testError" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Error" status="4" time="%s" size="-1" groups="default" exceptionMessage=""/>
  <test className="vendor\project\StatusTest" methodName="testIncomplete" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Incomplete" status="2" time="%s" size="-1" groups="default"/>
  <test className="vendor\project\StatusTest" methodName="testSkipped" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Skipped" status="1" time="%s" size="-1" groups="default"/>
  <test className="vendor\project\StatusTest" methodName="testRisky" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Risky" status="5" time="%s" size="-1" groups="default"/>
  <test className="vendor\project\StatusTest" methodName="testWarning" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Warning" status="6" time="%s" size="-1" groups="default"/>
  <test className="vendor\project\StatusTest" methodName="testSuccessWithMessage" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Success with message" status="0" time="%s" size="-1" groups="default"/>
  <test className="vendor\project\StatusTest" methodName="testFailureWithMessage" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Failure with message" status="3" time="%s" size="-1" groups="default" exceptionLine="%d" exceptionMessage="failure with custom message&#10;Failed asserting that false is true."/>
  <test className="vendor\project\StatusTest" methodName="testErrorWithMessage" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Error with message" status="4" time="%s" size="-1" groups="default" exceptionMessage="error with custom message"/>
  <test className="vendor\project\StatusTest" methodName="testIncompleteWithMessage" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Incomplete with message" status="2" time="%s" size="-1" groups="default"/>
  <test className="vendor\project\StatusTest" methodName="testSkippedWithMessage" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Skipped with message" status="1" time="%s" size="-1" groups="default"/>
  <test className="vendor\project\StatusTest" methodName="testRiskyWithMessage" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Risky with message" status="5" time="%s" size="-1" groups="default"/>
  <test className="vendor\project\StatusTest" methodName="testWarningWithMessage" prettifiedClassName="vendor\project\Status" prettifiedMethodName="Warning with message" status="6" time="%s" size="-1" groups="default"/>
</tests>


Time: %s, Memory: %s

There were 2 errors:

1) vendor\project\StatusTest::testError
RuntimeException:%w

%s%eStatusTest.php:%d

2) vendor\project\StatusTest::testErrorWithMessage
RuntimeException: error with custom message

%s%eStatusTest.php:%d

--

There were 2 warnings:

1) vendor\project\StatusTest::testWarning

%s%eStatusTest.php:%d

2) vendor\project\StatusTest::testWarningWithMessage
warning with custom message

%s%eStatusTest.php:%d

--

There were 2 failures:

1) vendor\project\StatusTest::testFailure
Failed asserting that false is true.

%s%eStatusTest.php:%d

2) vendor\project\StatusTest::testFailureWithMessage
failure with custom message
Failed asserting that false is true.

%s%eStatusTest.php:%d

--

There were 2 risky tests:

1) vendor\project\StatusTest::testRisky
This test did not perform any assertions

%s%eStatusTest.php:%d

2) vendor\project\StatusTest::testRiskyWithMessage
This test did not perform any assertions

%s%eStatusTest.php:%d

ERRORS!
Tests: 14, Assertions: 4, Errors: 2, Failures: 2, Warnings: 2, Skipped: 2, Incomplete: 2, Risky: 2.
