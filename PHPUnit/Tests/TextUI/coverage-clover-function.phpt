--TEST--
phpunit --coverage-clover php://stdout CoverageFunctionTest ../_files/CoverageFunctionTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--coverage-clover';
$_SERVER['argv'][3] = 'php://stdout';
$_SERVER['argv'][4] = 'CoverageFunctionTest';
$_SERVER['argv'][5] = dirname(dirname(__FILE__)) . '/_files/CoverageFunctionTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

.

Time: %i %s

OK (1 test, 0 assertions)

Writing code coverage data to XML file, this may take a moment.<?xml version="1.0" encoding="UTF-8"?>
<coverage generated="%i" phpunit="%s">
  <project name="CoverageTest" timestamp="%i">
    <file name="%s/CoveredFunction.php">
      <line num="4" type="stmt" count="1"/>
      <line num="5" type="stmt" count="1"/>
      <metrics loc="5" ncloc="5" classes="0" methods="0" coveredmethods="0" statements="2" coveredstatements="2" elements="2" coveredelements="2"/>
    </file>
    <metrics files="1" loc="5" ncloc="5" classes="0" methods="0" coveredmethods="0" statements="2" coveredstatements="2" elements="2" coveredelements="2"/>
  </project>
</coverage>
