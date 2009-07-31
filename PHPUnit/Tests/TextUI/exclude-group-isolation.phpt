--TEST--
phpunit --process-isolation --exclude-group balanceIsInitiallyZero BankAccountTest ../../Samples/BankAccount/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--process-isolation';
$_SERVER['argv'][3] = '--exclude-group';
$_SERVER['argv'][4] = 'balanceIsInitiallyZero';
$_SERVER['argv'][5] = 'BankAccountTest';
$_SERVER['argv'][6] = '../Samples/BankAccount/BankAccountTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

..

Time: %i %s

OK (2 tests, 2 assertions)
