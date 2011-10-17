--TEST--
phpunit --log-junit php://stdout TwoSubsuitesTest ../_files/TwoSubsuitesTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--log-junit';
$_SERVER['argv'][3] = 'php://stdout';
$_SERVER['argv'][4] = 'TwoSubsuitesTest';
$_SERVER['argv'][5] = dirname(dirname(__FILE__)) . '/_files/TwoSubsuitesTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

......<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="TwoSubsuitesTest" file="%s/TwoSubsuitesTest.php" tests="6" assertions="0" failures="0" errors="0" time="%f">
    <testsuite name="TwoSubsuitesTest::testNoop" tests="3" assertions="0" failures="0" errors="0" time="%f">
      <testcase name="testNoop" class="TwoSubsuitesTest" file="%s/TwoSubsuitesTest.php" line="7" assertions="0" time="%f"/>
      <testcase name="testNoop" class="TwoSubsuitesTest" file="%s/TwoSubsuitesTest.php" line="7" assertions="0" time="%f"/>
      <testcase name="testNoop" class="TwoSubsuitesTest" file="%s/TwoSubsuitesTest.php" line="7" assertions="0" time="%f"/>
    </testsuite>
    <testsuite name="TwoSubsuitesTest::testNoop2" tests="3" assertions="0" failures="0" errors="0" time="%f">
      <testcase name="testNoop2" class="TwoSubsuitesTest" file="%s/TwoSubsuitesTest.php" line="14" assertions="0" time="%f"/>
      <testcase name="testNoop2" class="TwoSubsuitesTest" file="%s/TwoSubsuitesTest.php" line="14" assertions="0" time="%f"/>
      <testcase name="testNoop2" class="TwoSubsuitesTest" file="%s/TwoSubsuitesTest.php" line="14" assertions="0" time="%f"/>
    </testsuite>
  </testsuite>
</testsuites>


Time: %i %s, Memory: %sMb

OK (6 tests, 0 assertions)
