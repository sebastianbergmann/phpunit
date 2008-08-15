--TEST--
phpunit --log-json php://stdout BankAccountTest ../../Samples/BankAccount/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--log-json';
$_SERVER['argv'][2] = 'php://stdout';
$_SERVER['argv'][3] = 'BankAccountTest';
$_SERVER['argv'][4] = '../Samples/BankAccount/BankAccountTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

{"event":"suiteStart","suite":"BankAccountTest","tests":3}.{"event":"test","suite":"BankAccountTest","test":"testBalanceIsInitiallyZero(BankAccountTest)","status":"pass","time":%f,"trace":[],"message":""}.{"event":"test","suite":"BankAccountTest","test":"testBalanceCannotBecomeNegative(BankAccountTest)","status":"pass","time":%f,"trace":[],"message":""}.{"event":"test","suite":"BankAccountTest","test":"testBalanceCannotBecomeNegative2(BankAccountTest)","status":"pass","time":%f,"trace":[],"message":""}

Time: %i seconds

OK (3 tests, 3 assertions)
