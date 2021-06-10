--TEST--
phpunit --list-tests-xml ../../_files/DataProviderTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--list-tests-xml';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../_files/DataProviderTest.php';

require __DIR__ . '/../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

<?xml version="1.0"?>
<tests>
 <testCaseClass name="PHPUnit\TestFixture\DataProviderTest">
  <testCaseMethod name="testAdd" groups="default" dataSet="#0"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#1"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#2"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#3"/>
 </testCaseClass>
</tests>%A
