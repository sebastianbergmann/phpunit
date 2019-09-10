--TEST--
phpunit -c ../../_files/configuration.custom-printer.xml --debug ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '-c',
    \realpath(__DIR__ . '/_files/configuration.custom-printer.xml'),
    '--debug',
    \realpath(__DIR__ . '/../../_files/BankAccountTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test 'BankAccountTest::testBalanceIsInitiallyZero' started
Test 'BankAccountTest::testBalanceIsInitiallyZero' ended
Test 'BankAccountTest::testBalanceCannotBecomeNegative' started
Test 'BankAccountTest::testBalanceCannotBecomeNegative' ended
Test 'BankAccountTest::testBalanceCannotBecomeNegative2' started
Test 'BankAccountTest::testBalanceCannotBecomeNegative2' ended


Time: %s, Memory: %s

OK (3 tests, 3 assertions)
