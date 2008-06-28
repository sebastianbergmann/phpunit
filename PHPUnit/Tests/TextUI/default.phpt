--TEST--
phpunit BankAccountTest ../../Samples/BankAccount/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = 'BankAccountTest';
$_SERVER['argv'][2] = '../Samples/BankAccount/BankAccountTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';

PHPUnit_TextUI_Command::main();
?>
--EXPECT--
PHPUnit @package_version@ by Sebastian Bergmann.

...

Time: 0 seconds

OK (3 tests, 3 assertions)
