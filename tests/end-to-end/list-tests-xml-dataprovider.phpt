--TEST--
phpunit --list-tests-xml ../../_files/DataProviderTest.php
--FILE--
<?php declare(strict_types=1);
$target = sys_get_temp_dir() . DIRECTORY_SEPARATOR . sha1(__FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--list-tests-xml';
$_SERVER['argv'][] = $target;
$_SERVER['argv'][] = __DIR__ . '/../_files/DataProviderTest.php';

require_once __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main(false);

print file_get_contents($target);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Wrote list of tests that would have been run to %s
<?xml version="1.0"?>
<tests>
 <testCaseClass name="PHPUnit\TestFixture\DataProviderTest">
  <testCaseMethod name="testAdd" groups="default" dataSet="#0"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#1"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#2"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#3"/>
 </testCaseClass>
</tests>
--CLEAN--
<?php declare(strict_types=1);
unlink(sys_get_temp_dir() . DIRECTORY_SEPARATOR . sha1(__FILE__));
