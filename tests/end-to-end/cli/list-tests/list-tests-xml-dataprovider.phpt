--TEST--
phpunit --list-tests-xml ../../../_files/DataProviderTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--list-tests-xml';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../../../_files/DataProviderTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

<?xml version="1.0"?>
<tests>
 <testCaseClass name="PHPUnit\TestFixture\DataProviderTest">
  <testCaseMethod id="PHPUnit\TestFixture\DataProviderTest::testAdd#0" name="testAdd" groups="default"/>
  <testCaseMethod id="PHPUnit\TestFixture\DataProviderTest::testAdd#1" name="testAdd" groups="default"/>
  <testCaseMethod id="PHPUnit\TestFixture\DataProviderTest::testAdd#2" name="testAdd" groups="default"/>
  <testCaseMethod id="PHPUnit\TestFixture\DataProviderTest::testAdd#3" name="testAdd" groups="default"/>
 </testCaseClass>
</tests>%A
