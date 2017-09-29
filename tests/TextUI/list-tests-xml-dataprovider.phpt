--TEST--
phpunit --list-tests-xml DataProviderTest ../_files/DataProviderTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--list-tests-xml';
$_SERVER['argv'][3] = 'DataProviderTest';
$_SERVER['argv'][4] = __DIR__ . '/../_files/DataProviderTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
<?xml version="1.0"?>
<tests>
 <testCaseClass name="DataProviderTest">
  <testCaseMethod name="testAdd" groups="default" dataSet="#0"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#1"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#2"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#3"/>
 </testCaseClass>
</tests>
