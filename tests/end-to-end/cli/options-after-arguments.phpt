--TEST--
phpunit BankAccountTest ../../_files/BankAccountTest.php --colors
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '--no-configuration',
    \realpath(__DIR__ . '/../../_files/BankAccountTest.php'),
    '--colors=always',
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

%s[30;42mOK (3 tests, 3 assertions)%s[0m
