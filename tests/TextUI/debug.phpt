--TEST--
phpunit --debug BankAccountTest ../_files/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--debug';
$_SERVER['argv'][3] = 'BankAccountTest';
$_SERVER['argv'][4] = __DIR__ . '/../_files/BankAccountTest.php';

require __DIR__ . '/../bootstrap.php';
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
