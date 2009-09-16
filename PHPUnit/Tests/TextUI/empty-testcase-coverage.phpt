--TEST--
phpunit --coverage-clover php://stdout EmptyTestCaseTest ../_files/EmptyTestCaseTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--coverage-clover';
$_SERVER['argv'][3] = 'php://stdout';
$_SERVER['argv'][4] = 'EmptyTestCaseTest';
$_SERVER['argv'][5] = dirname(dirname(__FILE__)) . '/_files/EmptyTestCaseTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

F

Time: %i %s

There was 1 failure:

1) Warning
No tests found in class "EmptyTestCaseTest".

%s:%i

FAILURES!
Tests: 1, Assertions: 0, Failures: 1.

Writing code coverage data to XML file, this may take a moment.<?xml version="1.0" encoding="UTF-8"?>
<coverage generated="%i" phpunit="%s">
  <project name="EmptyTestCaseTest" timestamp="%i">
    <metrics files="0" loc="0" ncloc="0" classes="0" methods="0" coveredmethods="0" statements="0" coveredstatements="0" elements="0" coveredelements="0"/>
  </project>
</coverage>
