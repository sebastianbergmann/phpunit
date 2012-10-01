--TEST--
phpunit --log-junit php://stdout BankAccountTest ../_files/BankAccountTest.php
--FILE--
<?php
define('PHPUNIT_TESTSUITE', TRUE);

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--log-junit';
$_SERVER['argv'][3] = 'php://stdout';
$_SERVER['argv'][4] = 'BankAccountTest';
$_SERVER['argv'][5] = dirname(__FILE__).'/../_files/BankAccountTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

...<?xml version="1.0" encoding="UTF-8"?>
<testsuites>
  <testsuite name="BankAccountTest" file="%sBankAccountTest.php" fullPackage="PHPUnit" package="PHPUnit" tests="3" assertions="3" failures="0" errors="0" time="%f">
    <testcase name="testBalanceIsInitiallyZero" class="BankAccountTest" file="%sBankAccountTest.php" line="72" assertions="1" time="%f"/>
    <testcase name="testBalanceCannotBecomeNegative" class="BankAccountTest" file="%sBankAccountTest.php" line="82" assertions="1" time="%f"/>
    <testcase name="testBalanceCannotBecomeNegative2" class="BankAccountTest" file="%sBankAccountTest.php" line="102" assertions="1" time="%f"/>
  </testsuite>
</testsuites>


Time: %i %s, Memory: %sMb

OK (3 tests, 3 assertions)
