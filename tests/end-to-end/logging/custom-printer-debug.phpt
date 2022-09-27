--TEST--
phpunit -c ../../_files/configuration.custom-printer.xml --debug ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '-c';
$_SERVER['argv'][] = \realpath(__DIR__ . '/_files/configuration.custom-printer.xml');
$_SERVER['argv'][] = '--debug';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/BankAccountTest.php');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

Test 'PHPUnit\TestFixture\BankAccountTest::testBalanceIsInitiallyZero' started
Test 'PHPUnit\TestFixture\BankAccountTest::testBalanceIsInitiallyZero' ended
Test 'PHPUnit\TestFixture\BankAccountTest::testBalanceCannotBecomeNegative' started
Test 'PHPUnit\TestFixture\BankAccountTest::testBalanceCannotBecomeNegative' ended
Test 'PHPUnit\TestFixture\BankAccountTest::testBalanceCannotBecomeNegative2' started
Test 'PHPUnit\TestFixture\BankAccountTest::testBalanceCannotBecomeNegative2' ended


Time: %s, Memory: %s

OK (3 tests, 3 assertions)
