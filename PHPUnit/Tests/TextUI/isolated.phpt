--TEST--
phpunit --process-isolation BankAccountTest ../../Samples/BankAccount/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--process-isolation';
$_SERVER['argv'][3] = 'BankAccountTest';
$_SERVER['argv'][4] = '../Samples/BankAccount/BankAccountTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

...

Time: %i seconds

OK (3 tests, 3 assertions)
