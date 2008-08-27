--TEST--
phpunit --log-xml php://stdout BankAccountTest ../../Samples/BankAccount/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--log-xml';
$_SERVER['argv'][2] = 'php://stdout';
$_SERVER['argv'][3] = 'BankAccountTest';
$_SERVER['argv'][4] = '../Samples/BankAccount/BankAccountTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/TextUI/Command.php';
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

...<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="BankAccountTest" file="%s/BankAccountTest.php" fullPackage="PHPUnit" category="Testing" package="PHPUnit" tests="3" assertions="3" failures="0" errors="0" time="%f">
    <testcase name="testBalanceIsInitiallyZero" class="BankAccountTest" file="%s/BankAccountTest.php" line="76" assertions="1" time="%f"/>
    <testcase name="testBalanceCannotBecomeNegative" class="BankAccountTest" file="%s/BankAccountTest.php" line="86" assertions="1" time="%f"/>
    <testcase name="testBalanceCannotBecomeNegative2" class="BankAccountTest" file="%s/BankAccountTest.php" line="106" assertions="1" time="%f"/>
  </testsuite>
</testsuites>


Time: %i seconds

OK (3 tests, 3 assertions)
