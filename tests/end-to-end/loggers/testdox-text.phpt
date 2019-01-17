--TEST--
phpunit --testdox-text php://stdout BankAccountTest ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '--no-configuration',
    '--testdox-text',
    'php://stdout',
    'BankAccountTest',
    \realpath(__DIR__ . '/../../_files/BankAccountTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

BankAccount
...                                                                 3 / 3 (100%) [x] Balance is initially zero
 [x] Balance cannot become negative
 [x] Balance cannot become negative



Time: %s, Memory: %s

OK (3 tests, 3 assertions)
