--TEST--
phpunit --coverage-clover php://stdout BankAccountTest ../../Samples/BankAccount/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--coverage-clover';
$_SERVER['argv'][3] = 'php://stdout';
$_SERVER['argv'][4] = 'BankAccountTest';
$_SERVER['argv'][5] = '../Samples/BankAccount/BankAccountTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

...

Time: %i %s

OK (3 tests, 3 assertions)

Writing code coverage data to XML file, this may take a moment.<?xml version="1.0" encoding="UTF-8"?>
<coverage generated="%i" phpunit="%s">
  <project name="BankAccountTest" timestamp="%i">
    <file name="%sBankAccount.php">
      <class name="BankAccountException" namespace="global" fullPackage="PHPUnit" category="Testing" package="PHPUnit">
        <metrics methods="0" coveredmethods="0" statements="0" coveredstatements="0" elements="0" coveredelements="0"/>
      </class>
      <class name="BankAccount" namespace="global" fullPackage="PHPUnit" category="Testing" package="PHPUnit">
        <metrics methods="4" coveredmethods="3" statements="10" coveredstatements="3" elements="14" coveredelements="6"/>
      </class>
      <line num="74" type="method" name="getBalance" count="1"/>
      <line num="76" type="stmt" count="1"/>
      <line num="85" type="method" name="setBalance" count="0"/>
      <line num="87" type="stmt" count="0"/>
      <line num="88" type="stmt" count="0"/>
      <line num="89" type="stmt" count="0"/>
      <line num="90" type="stmt" count="0"/>
      <line num="92" type="stmt" count="0"/>
      <line num="100" type="method" name="depositMoney" count="1"/>
      <line num="102" type="stmt" count="1"/>
      <line num="104" type="stmt" count="0"/>
      <line num="113" type="method" name="withdrawMoney" count="1"/>
      <line num="115" type="stmt" count="1"/>
      <line num="117" type="stmt" count="0"/>
      <metrics loc="119" ncloc="36" classes="2" methods="4" coveredmethods="3" statements="10" coveredstatements="3" elements="14" coveredelements="6"/>
    </file>
    <metrics files="1" loc="119" ncloc="36" classes="2" methods="4" coveredmethods="3" statements="10" coveredstatements="3" elements="14" coveredelements="6"/>
  </project>
</coverage>
