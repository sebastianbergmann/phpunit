--TEST--
phpunit --testdox-text php://stdout BankAccountTest ../../Samples/BankAccount/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--testdox-text';
$_SERVER['argv'][2] = 'php://stdout';
$_SERVER['argv'][3] = 'BankAccountTest';
$_SERVER['argv'][4] = '../Samples/BankAccount/BankAccountTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

BankAccount
... [x] Balance is initially zero
 [x] Balance cannot become negative



Time: %i seconds

OK (3 tests, 3 assertions)
