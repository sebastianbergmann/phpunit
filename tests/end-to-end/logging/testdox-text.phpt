--TEST--
phpunit --testdox-text php://stdout ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox-text';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/BankAccountTest.php');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Bank Account (PHPUnit\TestFixture\BankAccount)
...                                                                 3 / 3 (100%) [x] Balance is initially zero
 [x] Balance cannot become negative
 [x] Balance cannot become negative



Time: %s, Memory: %s

OK (3 tests, 3 assertions)
