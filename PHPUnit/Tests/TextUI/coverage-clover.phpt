--TEST--
phpunit --coverage-clover php://stdout BankAccountTest ../../Samples/BankAccount/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--coverage-clover';
$_SERVER['argv'][2] = 'php://stdout';
$_SERVER['argv'][3] = 'BankAccountTest';
$_SERVER['argv'][4] = '../Samples/BankAccount/BankAccountTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

...

Time: %i seconds

OK (3 tests, 3 assertions)

Writing code coverage data to XML file, this may take a moment.<?xml version="1.0" encoding="UTF-8"?>
<coverage generated="%d" phpunit="%s">
  <project name="BankAccountTest" timestamp="%d">
    <file name="%s/BankAccount.php">
      <class name="BankAccountException" namespace="global" fullPackage="PHPUnit" category="Testing" package="PHPUnit">
        <metrics methods="0" coveredmethods="0" statements="0" coveredstatements="0" elements="0" coveredelements="0"/>
      </class>
      <class name="BankAccount" namespace="global" fullPackage="PHPUnit" category="Testing" package="PHPUnit">
        <metrics methods="4" coveredmethods="3" statements="9" coveredstatements="3" elements="13" coveredelements="6"/>
      </class>
      <line num="75" type="method" count="1"/>
      <line num="77" type="stmt" count="1"/>
      <line num="86" type="method" count="0"/>
      <line num="88" type="stmt" count="0"/>
      <line num="89" type="stmt" count="0"/>
      <line num="90" type="stmt" count="0"/>
      <line num="91" type="stmt" count="0"/>
      <line num="93" type="stmt" count="0"/>
      <line num="101" type="method" count="1"/>
      <line num="103" type="stmt" count="1"/>
      <line num="105" type="stmt" count="0"/>
      <line num="114" type="method" count="1"/>
      <line num="116" type="stmt" count="1"/>
      <line num="118" type="stmt" count="0"/>
      <metrics loc="121" ncloc="37" classes="2" methods="4" coveredmethods="3" statements="10" coveredstatements="3" elements="14" coveredelements="6"/>
    </file>
    <metrics files="1" loc="121" ncloc="37" classes="2" methods="4" coveredmethods="3" statements="10" coveredstatements="3" elements="14" coveredelements="6"/>
  </project>
</coverage>
