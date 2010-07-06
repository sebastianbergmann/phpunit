--TEST--
phpunit --assert-strict BankAccountTest ../Samples/BankAccount/BankAccountFailureTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--assert-strict';
$_SERVER['argv'][4] = 'BankAccountTest';
$_SERVER['argv'][5] = dirname(__FILE__).'/../../Samples/BankAccount/BankAccountFailureTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

I

Time: %i %s, Memory: %sMb

OK, but incomplete or skipped tests!
Tests: 1, Assertions: 0, Incomplete: 1.
