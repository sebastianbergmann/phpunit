--TEST--
phpunit BankAccountTest ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '--no-configuration',
    '--my-option=123',
    '--my-other-option',
    'BankAccountTest',
    \realpath(__DIR__ . '/../../_files/BankAccountTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';

require __DIR__ . '/_files/MyCommand.php';
MyCommand::main();
--EXPECTF--
MyCommand::myHandler 123
PHPUnit %s by Sebastian Bergmann and contributors.

...                                                                 3 / 3 (100%)

Time: %s, Memory: %s

OK (3 tests, 3 assertions)
