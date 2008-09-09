--TEST--
phpunit --log-xml php://stdout DataSetTest ../_files/DataSetTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--log-xml';
$_SERVER['argv'][2] = 'php://stdout';
$_SERVER['argv'][3] = 'DataSetTest';
$_SERVER['argv'][4] = dirname(dirname(__FILE__)) . '/_files/DataSetTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

..F.<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="DataSetTest" file="%s/DataSetTest.php" tests="4" assertions="4" failures="1" errors="0" time="%f">
    <testsuite name="DataSetTest::testAdd" tests="4" assertions="4" failures="1" errors="0" time="%f">
      <testcase name="testAdd with data set #0" assertions="1" time="%f"/>
      <testcase name="testAdd with data set #1" assertions="1" time="%f"/>
      <testcase name="testAdd with data set #2" assertions="1" time="%f">
        <failure type="PHPUnit_Framework_ExpectationFailedException">testAdd(DataSetTest) with data set #2 (1, 1, 3)
Failed asserting that &lt;integer:2&gt; matches expected value &lt;integer:3&gt;.

%s:%i
%s:%i
</failure>
      </testcase>
      <testcase name="testAdd with data set #3" assertions="1" time="%f"/>
    </testsuite>
  </testsuite>
</testsuites>


Time: %i seconds

There was 1 failure:

1) testAdd(DataSetTest) with data set #2 (1, 1, 3)
Failed asserting that <integer:2> matches expected value <integer:3>.
%s:%i
%s:%i

FAILURES!
Tests: 4, Assertions: 4, Failures: 1.

