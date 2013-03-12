--TEST--
phpunit --debug BankAccountTest ../_files/BankAccountTest.php
--FILE--
<?php
define('PHPUNIT_TESTSUITE', TRUE);

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--debug';
$_SERVER['argv'][3] = 'BankAccountTest';
$_SERVER['argv'][4] = dirname(__FILE__).'/../_files/BankAccountTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.


Starting test 'BankAccountTest::testBalanceIsInitiallyZero'.
.
Starting test 'BankAccountTest::testBalanceCannotBecomeNegative'.
.
Starting test 'BankAccountTest::testBalanceCannotBecomeNegative2'.
.

Time: %s, Memory: %sMb

OK (3 tests, 3 assertions)
