--TEST--
phpunit --list-tests-xml ../../_files/DataProviderTest.php
--FILE--
<?php declare(strict_types=1);
$target = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--list-tests-xml';
$_SERVER['argv'][3] = $target;
$_SERVER['argv'][4] = __DIR__ . '/../_files/DataProviderTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main(false);

print file_get_contents($target);

unlink($target);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Wrote list of tests that would have been run to %s
<?xml version="1.0"?>
<tests>
 <testCaseClass name="DataProviderTest">
  <testCaseMethod name="testAdd" groups="default" dataSet="#0"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#1"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#2"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#3"/>
 </testCaseClass>
</tests>
