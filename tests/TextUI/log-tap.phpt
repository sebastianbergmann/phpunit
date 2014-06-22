--TEST--
phpunit --log-tap php://stdout BankAccountTest ../_files/BankAccountTest.php
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--log-tap';
$_SERVER['argv'][3] = 'php://stdout';
$_SERVER['argv'][4] = 'BankAccountTest';
$_SERVER['argv'][5] = dirname(__FILE__).'/../_files/BankAccountTest.php';

require __DIR__ . '/../bootstrap.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
   ___  __ _____  __  __     _ __
  / _ \/ // / _ \/ / / /__  (_) /_
 / ___/ _  / ___/ /_/ / _ \/ / __/
/_/  /_//_/_/   \____/_//_/_/\__/

PHPUnit %s by Sebastian Bergmann.

TAP version 13
.ok 1 - BankAccountTest::testBalanceIsInitiallyZero
.ok 2 - BankAccountTest::testBalanceCannotBecomeNegative
.ok 3 - BankAccountTest::testBalanceCannotBecomeNegative2
1..3


Time: %s, Memory: %sMb

OK (3 tests, 3 assertions)
