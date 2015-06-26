--TEST--
GH-1767: Test that shows some tests are skipped from JUnit xml log
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--disallow-test-output';
$_SERVER['argv'][2] = '--log-junit=/tmp/issue1767.xml';
$_SERVER['argv'][3] = 'Issue1570Test';
$_SERVER['argv'][4] = dirname(__FILE__) . '/1767/Issue1767Test.php';

require __DIR__ . '/../../bootstrap.php';
PHPUnit_TextUI_Command::main(false);

echo file_get_contents('/tmp/issue1767.xml');
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

FSFF

Time: %s ms, Memory: %sMb

There were 3 failures:

1) Issue1767Test::testTrigger
This test will skip the next good test from JUnit xml report

%s/Issue1767Test.php:%d

2) Issue1767Test::testShouldNotBeSkipped
This test SHOULD NOT be skipped from JUnit xml report

%s/Issue1767Test.php:%d

3) Issue1767Test::testAreNotSkipped
This is the next failing test showing up in JUnit xml report

%s/Issue1767Test.php:%d

FAILURES!
Tests: 3, Assertions: 0, Failures: 3, Skipped: 1.
<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="Issue1767Test" file="%s/Issue1767Test.php" tests="3" assertions="0" failures="3" errors="0" time="%s">
    <testcase name="testTrigger" class="Issue1767Test" file="%s/Issue1767Test.php" line="%d" assertions="0" time="%s">
      <failure type="PHPUnit_Framework_AssertionFailedError">Issue1767Test::testTrigger
This test will skip the next good test from JUnit xml report

%s/Issue1767Test.php:%d
</failure>
    </testcase>
    <testcase name="testShouldNotBeSkipped" class="Issue1767Test" file="%s/Issue1767Test.php" line="%d" assertions="0" time="%s">
      <failure type="PHPUnit_Framework_AssertionFailedError">Issue1767Test::testShouldNotBeSkipped
This test SHOULD NOT be skipped from JUnit xml report

%s/Issue1767Test.php:%d
</failure>
    </testcase>
    <testcase name="testAreNotSkipped" class="Issue1767Test" file="%s/Issue1767Test.php" line="%d" assertions="0" time="%s">
      <failure type="PHPUnit_Framework_AssertionFailedError">Issue1767Test::testAreNotSkipped
This is the next failing test showing up in JUnit xml report

%s/Issue1767Test.php:%d
</failure>
    </testcase>
  </testsuite>
</testsuites>
