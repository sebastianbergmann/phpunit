--TEST--
phpunit --log-junit php://stdout StatusTest ../_files/StatusTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--log-junit';
$_SERVER['argv'][3] = 'php://stdout';
$_SERVER['argv'][4] = 'StatusTest';
$_SERVER['argv'][5] = __DIR__ . '/../_files/StatusTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

.FEIS.<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="StatusTest" file="%s/StatusTest.php" tests="4" assertions="2" failures="1" errors="1" time="%s">
    <testcase name="testSuccess" class="StatusTest" file="%s/StatusTest.php" line="%d" assertions="1" time="%s"/>
    <testcase name="testFailure" class="StatusTest" file="%s/StatusTest.php" line="%d" assertions="1" time="%s">
      <failure type="PHPUnit_Framework_ExpectationFailedException">StatusTest::testFailure
Failed asserting that false is true.

%s/StatusTest.php:%d
</failure>
    </testcase>
    <testcase name="testError" class="StatusTest" file="%s/StatusTest.php" line="%d" assertions="0" time="%s">
      <error type="Exception">StatusTest::testError
Exception: 

%s/StatusTest.php:%d
</error>
    </testcase>
    <testcase name="testRisky" class="StatusTest" file="%s/StatusTest.php" line="%d" assertions="0" time="%s"/>
  </testsuite>
</testsuites>


Time: %s, Memory: %s

There was 1 error:

1) StatusTest::testError
Exception: 

%s/StatusTest.php:%d

--

There was 1 failure:

1) StatusTest::testFailure
Failed asserting that false is true.

%s/StatusTest.php:%d

FAILURES!
Tests: 6, Assertions: 2, Errors: 1, Failures: 1, Skipped: 1, Incomplete: 1.