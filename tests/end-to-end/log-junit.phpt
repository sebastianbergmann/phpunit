--TEST--
phpunit --log-junit php://stdout StatusTest ../../_files/StatusTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--log-junit';
$_SERVER['argv'][3] = 'php://stdout';
$_SERVER['argv'][4] = 'StatusTest';
$_SERVER['argv'][5] = __DIR__ . '/../_files/StatusTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.FEISRW                                                             7 / 7 (100%)<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="vendor\project\StatusTest" file="%s%eStatusTest.php" tests="7" assertions="2" errors="2" failures="2" skipped="2" time="%s">
    <testcase name="testSuccess" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="1" time="%s"/>
    <testcase name="testFailure" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="1" time="%s">
      <failure type="PHPUnit\Framework\ExpectationFailedException">vendor\project\StatusTest::testFailure
Failed asserting that false is true.

%s%eStatusTest.php:%d
</failure>
    </testcase>
    <testcase name="testError" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="0" time="%s">
      <error type="RuntimeException">vendor\project\StatusTest::testError
RuntimeException:%w

%s%eStatusTest.php:%d
</error>
    </testcase>
    <testcase name="testIncomplete" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="0" time="%s">
      <skipped/>
    </testcase>
    <testcase name="testSkipped" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="0" time="%s">
      <skipped/>
    </testcase>
    <testcase name="testRisky" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="0" time="%s">
      <error type="PHPUnit\Framework\RiskyTestError">Risky Test
</error>
    </testcase>
    <testcase name="testWarning" class="vendor\project\StatusTest" classname="vendor.project.StatusTest" file="%s%eStatusTest.php" line="%d" assertions="0" time="%s">
      <warning type="PHPUnit\Framework\Warning">vendor\project\StatusTest::testWarning

%s%eStatusTest.php:%d
</warning>
    </testcase>
  </testsuite>
</testsuites>


Time: %s, Memory: %s

There was 1 error:

1) vendor\project\StatusTest::testError
RuntimeException:%w

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

%s:42

ERRORS!
Tests: 7, Assertions: 2, Errors: 1, Failures: 1, Warnings: 1, Skipped: 1, Incomplete: 1, Risky: 1.
