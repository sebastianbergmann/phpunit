--TEST--
phpunit --list-tests DataProviderTest ../_files/DataProviderTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--list-tests';
$_SERVER['argv'][3] = 'DataProviderTest';
$_SERVER['argv'][4] = __DIR__ . '/../_files/DataProviderTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Available test(s):
 - DataProviderTest::testAdd#0
 - DataProviderTest::testAdd#1
 - DataProviderTest::testAdd#2
 - DataProviderTest::testAdd#3
