--TEST--
phpunit --testdox-xml php://stdout ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$output = tempnam(sys_get_temp_dir(), __FILE__);

$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--testdox-xml';
$_SERVER['argv'][] = $output;
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/BankAccountTest.php');

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);

print file_get_contents($output);

unlink($output);
--EXPECTF--
<?xml version="1.0" encoding="UTF-8"?>
<tests>
 <test className="PHPUnit\TestFixture\BankAccountTest" methodName="testBalanceIsInitiallyZero" prettifiedClassName="Bank Account (PHPUnit\TestFixture\BankAccount)" prettifiedMethodName="Balance is initially zero" size="unknown" time="%s" status="success" given="a fresh bank account" givenStartLine="32" when="I ask it for its balance" whenStartLine="35" then="I should get 0" thenStartLine="38">
  <group name="balanceIsInitiallyZero"/>
  <group name="specification"/>
  <group name="1234"/>
  <covers target="BankAccount::getBalance"/>
 </test>
 <test className="PHPUnit\TestFixture\BankAccountTest" methodName="testBalanceCannotBecomeNegative" prettifiedClassName="Bank Account (PHPUnit\TestFixture\BankAccount)" prettifiedMethodName="Balance cannot become negative" size="unknown" time="%s" status="success">
  <group name="balanceCannotBecomeNegative"/>
  <group name="specification"/>
  <covers target="BankAccount::withdrawMoney"/>
 </test>
 <test className="PHPUnit\TestFixture\BankAccountTest" methodName="testBalanceCannotBecomeNegative2" prettifiedClassName="Bank Account (PHPUnit\TestFixture\BankAccount)" prettifiedMethodName="Balance cannot become negative" size="unknown" time="%s" status="success">
  <group name="balanceCannotBecomeNegative"/>
  <group name="specification"/>
  <covers target="BankAccount::depositMoney"/>
 </test>
</tests>
