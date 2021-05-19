--TEST--
phpunit --testdox-xml php://stdout ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--testdox-xml';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/BankAccountTest.php');

require __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

...                                                                 3 / 3 (100%)<?xml version="1.0" encoding="UTF-8"?>
<tests>
  <test className="PHPUnit\TestFixture\BankAccountTest" methodName="testBalanceIsInitiallyZero" prettifiedClassName="Bank Account (PHPUnit\TestFixture\BankAccount)" prettifiedMethodName="Balance is initially zero" status="success" time="%s" size="unknown" given="a fresh bank account" givenStartLine="31" when="I ask it for its balance" whenStartLine="34" then="I should get 0" thenStartLine="37">
    <group name="balanceIsInitiallyZero"/>
    <group name="specification"/>
    <group name="1234"/>
    <covers target="BankAccount::getBalance"/>
  </test>
  <test className="PHPUnit\TestFixture\BankAccountTest" methodName="testBalanceCannotBecomeNegative" prettifiedClassName="Bank Account (PHPUnit\TestFixture\BankAccount)" prettifiedMethodName="Balance cannot become negative" status="success" time="%s" size="unknown">
    <group name="balanceCannotBecomeNegative"/>
    <group name="specification"/>
    <covers target="BankAccount::withdrawMoney"/>
  </test>
  <test className="PHPUnit\TestFixture\BankAccountTest" methodName="testBalanceCannotBecomeNegative2" prettifiedClassName="Bank Account (PHPUnit\TestFixture\BankAccount)" prettifiedMethodName="Balance cannot become negative" status="success" time="%s" size="unknown">
    <group name="balanceCannotBecomeNegative"/>
    <group name="specification"/>
    <covers target="BankAccount::depositMoney"/>
  </test>
</tests>


Time: %s, Memory: %s

OK (3 tests, 3 assertions)
