--TEST--
phpunit --log-json php://stdout BankAccountTest ../_files/BankAccountTest.php
--SKIPIF--
<?php
if (version_compare(PHP_VERSION, '5.4.0') >= 0) print 'skip: PHP < 5.4 required';
?>
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--log-json';
$_SERVER['argv'][3] = 'php://stdout';
$_SERVER['argv'][4] = 'BankAccountTest';
$_SERVER['argv'][5] = dirname(__FILE__).'/../_files/BankAccountTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
   ___  __ _____  __  __     _ __
  / _ \/ // / _ \/ / / /__  (_) /_
 / ___/ _  / ___/ /_/ / _ \/ / __/
/_/  /_//_/_/   \____/_//_/_/\__/

PHPUnit %s by Sebastian Bergmann.

{"event":"suiteStart","suite":"BankAccountTest","tests":3}{"event":"testStart","suite":"BankAccountTest","test":"BankAccountTest::testBalanceIsInitiallyZero"}.{"event":"test","suite":"BankAccountTest","test":"BankAccountTest::testBalanceIsInitiallyZero","status":"pass","time":%f,"trace":[],"message":"","output":""}{"event":"testStart","suite":"BankAccountTest","test":"BankAccountTest::testBalanceCannotBecomeNegative"}.{"event":"test","suite":"BankAccountTest","test":"BankAccountTest::testBalanceCannotBecomeNegative","status":"pass","time":%f,"trace":[],"message":"","output":""}{"event":"testStart","suite":"BankAccountTest","test":"BankAccountTest::testBalanceCannotBecomeNegative2"}.{"event":"test","suite":"BankAccountTest","test":"BankAccountTest::testBalanceCannotBecomeNegative2","status":"pass","time":%f,"trace":[],"message":"","output":""}

Time: %s, Memory: %sMb

OK (3 tests, 3 assertions)
