--TEST--
phpunit --log-tap php://stdout BankAccountTest ../../Samples/BankAccount/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--log-tap';
$_SERVER['argv'][3] = 'php://stdout';
$_SERVER['argv'][4] = 'BankAccountTest';
$_SERVER['argv'][5] = '../Samples/BankAccount/BankAccountTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

TAP version 13
.ok 1 - testBalanceIsInitiallyZero(BankAccountTest)
.ok 2 - testBalanceCannotBecomeNegative(BankAccountTest)
.ok 3 - testBalanceCannotBecomeNegative2(BankAccountTest)
1..3


Time: %i seconds

OK (3 tests, 3 assertions)
