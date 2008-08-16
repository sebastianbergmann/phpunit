--TEST--
phpunit --coverage-clover php://stdout CoveragePrivateTest ../_files/CoveragePrivateTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--coverage-clover';
$_SERVER['argv'][2] = 'php://stdout';
$_SERVER['argv'][3] = 'CoveragePrivateTest';
$_SERVER['argv'][4] = dirname(dirname(__FILE__)) . '/_files/CoveragePrivateTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

.

Time: %i seconds

OK (1 test, 0 assertions)

Writing code coverage data to XML file, this may take a moment.<?xml version="1.0" encoding="UTF-8"?>
<coverage generated="%i" phpunit="%s">
  <project name="CoverageTest" timestamp="%i">
    <file name="%s/CoveredClass.php">
      <class name="CoveredParentClass" namespace="global">
        <metrics methods="3" coveredmethods="0" statements="2" coveredstatements="0" elements="5" coveredelements="0"/>
      </class>
      <class name="CoveredClass" namespace="global">
        <metrics methods="3" coveredmethods="1" statements="4" coveredstatements="1" elements="7" coveredelements="2"/>
      </class>
      <line num="4" type="method" count="0"/>
      <line num="6" type="stmt" count="0"/>
      <line num="8" type="method" count="0"/>
      <line num="10" type="stmt" count="0"/>
      <line num="11" type="stmt" count="0"/>
      <line num="13" type="method" count="0"/>
      <line num="15" type="stmt" count="0"/>
      <line num="16" type="stmt" count="0"/>
      <line num="21" type="method" count="1"/>
      <line num="23" type="stmt" count="1"/>
      <line num="25" type="method" count="0"/>
      <line num="27" type="stmt" count="0"/>
      <line num="28" type="stmt" count="0"/>
      <line num="29" type="stmt" count="0"/>
      <line num="31" type="method" count="0"/>
      <line num="33" type="stmt" count="0"/>
      <line num="34" type="stmt" count="0"/>
      <line num="35" type="stmt" count="0"/>
      <metrics loc="37" ncloc="37" classes="2" methods="6" coveredmethods="1" statements="12" coveredstatements="1" elements="18" coveredelements="2"/>
    </file>
    <metrics files="1" loc="37" ncloc="37" classes="2" methods="6" coveredmethods="1" statements="12" coveredstatements="1" elements="18" coveredelements="2"/>
  </project>
</coverage>

