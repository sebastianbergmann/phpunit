--TEST--
phpunit --group balanceIsInitiallyZero BankAccountTest ../../Samples/BankAccount/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--group';
$_SERVER['argv'][2] = 'balanceIsInitiallyZero';
$_SERVER['argv'][3] = 'BankAccountTest';
$_SERVER['argv'][4] = '../Samples/BankAccount/BankAccountTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
?>
--EXPECTF--
PHPUnit @package_version@ by Sebastian Bergmann.

.

Time: %i seconds

OK (1 test, 1 assertion)
