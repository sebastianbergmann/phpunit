--TEST--
phpunit ../../_files/BankAccountTest.php --colors
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/BankAccountTest.php');
$_SERVER['argv'][] = '--colors=always';

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

%s[30;42mOK (3 tests, 3 assertions)%s[0m
