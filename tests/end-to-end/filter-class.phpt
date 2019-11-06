--TEST--
phpunit --filter BankAccountTest ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--filter';
$_SERVER['argv'][3] = 'BankAccountTest';
$_SERVER['argv'][4] = __DIR__ . '/../_files/BankAccountTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

OK (3 tests, 3 assertions)
