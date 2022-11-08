--TEST--
phpunit --list-tests-xml ../../_files/DataProvider/DataProviderTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--list-tests-xml';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../../_files/DataProvider/DataProviderTest.php';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

<?xml version="1.0"?>
<tests>
 <testCaseClass name="PHPUnit\TestFixture\DataProvider\DataProviderTest">
  <testCaseMethod name="testAdd" groups="default" dataSet="#0"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#1"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#2"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#3"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#4"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#5"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#6"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#7"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#8"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#9"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#10"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#11"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#12"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#13"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#14"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#15"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#16"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#17"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#18"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#19"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#20"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#21"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#22"/>
  <testCaseMethod name="testAdd" groups="default" dataSet="#23"/>
 </testCaseClass>
</tests>%A
