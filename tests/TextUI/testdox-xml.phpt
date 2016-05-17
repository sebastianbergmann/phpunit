--TEST--
phpunit --testdox-xml php://stdout BankAccountTest ../_files/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--testdox-xml';
$_SERVER['argv'][3] = 'php://stdout';
$_SERVER['argv'][4] = 'BankAccountTest';
$_SERVER['argv'][5] = __DIR__ . '/../_files/BankAccountTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

...                                                                 3 / 3 (100%)<?xml version="1.0" encoding="UTF-8"?>
<tests>
  <test className="BankAccountTest" prettifiedClassName="BankAccount" methodName="testBalanceIsInitiallyZero" prettifiedMethodName="Balance is initially zero" status="0" size="-1" groups="balanceIsInitiallyZero,specification"/>
  <test className="BankAccountTest" prettifiedClassName="BankAccount" methodName="testBalanceCannotBecomeNegative" prettifiedMethodName="Balance cannot become negative" status="0" size="-1" groups="balanceCannotBecomeNegative,specification"/>
  <test className="BankAccountTest" prettifiedClassName="BankAccount" methodName="testBalanceCannotBecomeNegative2" prettifiedMethodName="Balance cannot become negative" status="0" size="-1" groups="balanceCannotBecomeNegative,specification"/>
</tests>


Time: %s, Memory: %s

OK (3 tests, 3 assertions)
