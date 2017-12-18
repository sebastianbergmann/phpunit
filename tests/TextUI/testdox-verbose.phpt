--TEST--
phpunit --testdox --verbose BankAccountTest ../_files/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--testdox';
$_SERVER['argv'][3] = '--verbose';
$_SERVER['argv'][4] = 'BankAccountTest';
$_SERVER['argv'][5] = __DIR__ . '/../_files/BankAccountTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Runtime:       %s

BankAccount
 ✔ Balance is initially zero [%f ms]
 ✔ Balance cannot become negative [%f ms]
 ✔ Balance cannot become negative [%f ms]

Time: %d ms, Memory: %fMB

OK (3 tests, 3 assertions)
