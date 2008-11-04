--TEST--
phpunit --tap BankAccountTest ../../Samples/BankAccount/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--tap';
$_SERVER['argv'][2] = 'BankAccountTest';
$_SERVER['argv'][3] = '../Samples/BankAccount/BankAccountTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
?>
--EXPECTF--
TAP version 13
ok 1 - testBalanceIsInitiallyZero(BankAccountTest)
ok 2 - testBalanceCannotBecomeNegative(BankAccountTest)
ok 3 - testBalanceCannotBecomeNegative2(BankAccountTest)
1..3
