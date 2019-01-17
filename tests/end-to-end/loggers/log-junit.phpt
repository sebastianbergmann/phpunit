--TEST--
phpunit --log-junit php://stdout StatusTest _files/StatusTest.php
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '--no-configuration',
    '--log-junit',
    'php://stdout',
    'StatusTest',
    \realpath(__DIR__ . '/../../basic/unit/StatusTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.FEISRW.FEISRW                                                    14 / 14 (100%)<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="vendor\project\StatusTest" file="%s%eStatusTest.php" tests="14" assertions="4" errors="4" failures="4" skipped="4" time="%f">
    <testcase name="testSuccess" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="1" time="%f"/>
    <testcase name="testFailure" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="1" time="%f">
      <failure type="PHPUnit\Framework\ExpectationFailedException">vendor\project\StatusTest::testFailure
Failed asserting that false is true.

%s%eStatusTest.php:%d
</failure>
    </testcase>
    <testcase name="testError" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="0" time="%f">
      <error type="RuntimeException">vendor\project\StatusTest::testError
RuntimeException:%w

%s%eStatusTest.php:%d
</error>
    </testcase>
    <testcase name="testIncomplete" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="0" time="%f">
      <skipped/>
    </testcase>
    <testcase name="testSkipped" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="0" time="%f">
      <skipped/>
    </testcase>
    <testcase name="testRisky" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="0" time="%f">
      <error type="PHPUnit\Framework\RiskyTestError">Risky Test
</error>
    </testcase>
    <testcase name="testWarning" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="0" time="%f">
      <warning type="PHPUnit\Framework\Warning">vendor\project\StatusTest::testWarning

%s%eStatusTest.php:%d
</warning>
    </testcase>
    <testcase name="testSuccessWithMessage" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="1" time="%f"/>
    <testcase name="testFailureWithMessage" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="1" time="%f">
      <failure type="PHPUnit\Framework\ExpectationFailedException">vendor\project\StatusTest::testFailureWithMessage
failure with custom message
Failed asserting that false is true.

%s%eStatusTest.php:%d
</failure>
    </testcase>
    <testcase name="testErrorWithMessage" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="0" time="%f">
      <error type="RuntimeException">vendor\project\StatusTest::testErrorWithMessage
RuntimeException: error with custom message

%s%eStatusTest.php:%d
</error>
    </testcase>
    <testcase name="testIncompleteWithMessage" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="0" time="%f">
      <skipped/>
    </testcase>
    <testcase name="testSkippedWithMessage" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="0" time="%f">
      <skipped/>
    </testcase>
    <testcase name="testRiskyWithMessage" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="0" time="%f">
      <error type="PHPUnit\Framework\RiskyTestError">Risky Test
</error>
    </testcase>
    <testcase name="testWarningWithMessage" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="0" time="%f">
      <warning type="PHPUnit\Framework\Warning">vendor\project\StatusTest::testWarningWithMessage
warning with custom message

%s%eStatusTest.php:%d
</warning>
    </testcase>
  </testsuite>
</testsuites>


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
