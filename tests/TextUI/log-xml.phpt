--TEST--
phpunit --log-junit php://stdout BankAccountTest ../_files/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--log-junit';
$_SERVER['argv'][3] = 'php://stdout';
$_SERVER['argv'][4] = 'BankAccountTest';
$_SERVER['argv'][5] = __DIR__ . '/../_files/BankAccountTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

...                                                                 3 / 3 (100%)<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="BankAccountTest" file="%sBankAccountTest.php" tests="3" assertions="3" errors="0" failures="0" skipped="0" time="%f">
    <testcase name="testBalanceIsInitiallyZero" class="BankAccountTest" file="%sBankAccountTest.php" line="30" assertions="1" time="%f"/>
    <testcase name="testBalanceCannotBecomeNegative" class="BankAccountTest" file="%sBankAccountTest.php" line="47" assertions="1" time="%f"/>
    <testcase name="testBalanceCannotBecomeNegative2" class="BankAccountTest" file="%sBankAccountTest.php" line="65" assertions="1" time="%f"/>
  </testsuite>
</testsuites>


Time: %s, Memory: %s

OK (3 tests, 3 assertions)
