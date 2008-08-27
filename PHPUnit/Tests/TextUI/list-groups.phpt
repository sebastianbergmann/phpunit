--TEST--
phpunit --list-groups BankAccountTest ../../Samples/BankAccount/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--list-groups';
$_SERVER['argv'][2] = 'BankAccountTest';
$_SERVER['argv'][3] = '../Samples/BankAccount/BankAccountTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
?>
--EXPECTF--
PHPUnit @package_version@ by Sebastian Bergmann.

Available test group(s):
 - balanceCannotBecomeNegative
 - balanceIsInitiallyZero
 - specification
