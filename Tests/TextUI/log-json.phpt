--TEST--
phpunit --log-json php://stdout BankAccountTest ../_files/BankAccountTest.php
--SKIPIF--
<?php
if (!defined("JSON_PRETTY_PRINT")) {
    echo "Skip: Test requires JSON_PRETTY_PRINT / PHP >= 5.4";
}
--FILE--
<?php
define('PHPUNIT_TESTSUITE', TRUE);

$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--log-json';
$_SERVER['argv'][3] = 'php://stdout';
$_SERVER['argv'][4] = 'BankAccountTest';
$_SERVER['argv'][5] = dirname(__FILE__).'/../_files/BankAccountTest.php';

require_once dirname(dirname(dirname(__FILE__))) . '/PHPUnit/Autoload.php';
PHPUnit_TextUI_Command::main();
?>
--EXPECTF--
PHPUnit %s by Sebastian Bergmann.

{
    "event": "suiteStart",
    "suite": "BankAccountTest",
    "tests": 3
}{
    "event": "testStart",
    "suite": "BankAccountTest",
    "test": "BankAccountTest::testBalanceIsInitiallyZero"
}.{
    "event": "test",
    "suite": "BankAccountTest",
    "test": "BankAccountTest::testBalanceIsInitiallyZero",
    "status": "pass",
    "time": %f,
    "trace": [

    ],
    "message": "",
    "output": ""
}{
    "event": "testStart",
    "suite": "BankAccountTest",
    "test": "BankAccountTest::testBalanceCannotBecomeNegative"
}.{
    "event": "test",
    "suite": "BankAccountTest",
    "test": "BankAccountTest::testBalanceCannotBecomeNegative",
    "status": "pass",
    "time": %f,
    "trace": [

    ],
    "message": "",
    "output": ""
}{
    "event": "testStart",
    "suite": "BankAccountTest",
    "test": "BankAccountTest::testBalanceCannotBecomeNegative2"
}.{
    "event": "test",
    "suite": "BankAccountTest",
    "test": "BankAccountTest::testBalanceCannotBecomeNegative2",
    "status": "pass",
    "time": %f,
    "trace": [

    ],
    "message": "",
    "output": ""
}

Time: %i %s, Memory: %sMb

OK (3 tests, 3 assertions)
