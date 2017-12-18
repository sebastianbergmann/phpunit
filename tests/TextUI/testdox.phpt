--TEST--
phpunit --testdox BankAccountTest ../_files/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--testdox';
$_SERVER['argv'][3] = 'BankAccountTest';
$_SERVER['argv'][4] = __DIR__ . '/../_files/BankAccountTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

BankAccount
 ✔ Balance is initially zero
 ✔ Balance cannot become negative
 ✔ Balance cannot become negative

Time: %d ms, Memory: %fMB

OK (3 tests, 3 assertions)
