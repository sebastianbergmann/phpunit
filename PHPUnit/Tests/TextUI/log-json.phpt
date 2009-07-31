--TEST--
phpunit --log-json php://stdout BankAccountTest ../../Samples/BankAccount/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--log-json';
$_SERVER['argv'][3] = 'php://stdout';
$_SERVER['argv'][4] = 'BankAccountTest';
$_SERVER['argv'][5] = '../Samples/BankAccount/BankAccountTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

{"event":"suiteStart","suite":"BankAccountTest","tests":3}.{"event":"test","suite":"BankAccountTest","test":"BankAccountTest::testBalanceIsInitiallyZero","status":"pass","time":%f,"trace":[],"message":""}.{"event":"test","suite":"BankAccountTest","test":"BankAccountTest::testBalanceCannotBecomeNegative","status":"pass","time":%f,"trace":[],"message":""}.{"event":"test","suite":"BankAccountTest","test":"BankAccountTest::testBalanceCannotBecomeNegative2","status":"pass","time":%f,"trace":[],"message":""}

Time: %i %s

OK (3 tests, 3 assertions)
