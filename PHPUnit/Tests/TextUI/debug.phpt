--TEST--
phpunit --debug BankAccountTest ../../Samples/BankAccount/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--debug';
$_SERVER['argv'][2] = 'BankAccountTest';
$_SERVER['argv'][3] = '../Samples/BankAccount/BankAccountTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.


Starting test 'testBalanceIsInitiallyZero(BankAccountTest)'.
.
Starting test 'testBalanceCannotBecomeNegative(BankAccountTest)'.
.
Starting test 'testBalanceCannotBecomeNegative2(BankAccountTest)'.
.

Time: %i seconds

OK (3 tests, 3 assertions)
