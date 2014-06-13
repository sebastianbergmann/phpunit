--TEST--
phpunit --log-json php://stdout BankAccountTest ../_files/BankAccountTest.php
--SKIPIF--
<?php
if (!((version_compare(PHP_VERSION, '5.4', '>=') && version_compare(PHP_VERSION, '5.4.27', '<=')) ||
    (version_compare(PHP_VERSION, '5.5', '>=') && version_compare(PHP_VERSION, '5.5.11', '<=')) ||
    (version_compare(PHP_VERSION, '5.6', '>=') && version_compare(PHP_VERSION, '5.6.0beta1', '<=') && PHP_VERSION != '5.6.0-dev') ||
    (defined('HHVM_VERSION') && version_compare(HHVM_VERSION, '3.2.0-dev', '<'))))
    print "skip: PHP 5.4.(0-27) or PHP 5.5.(0-11) or PHP 5.6.(0alpha1-0beta1) or HHVM < 3.2 required";
?>
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--log-json';
$_SERVER['argv'][3] = 'php://stdout';
$_SERVER['argv'][4] = 'BankAccountTest';
$_SERVER['argv'][5] = dirname(__FILE__).'/../_files/BankAccountTest.php';

require __DIR__ . '/../bootstrap.php';
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

Time: %s, Memory: %sMb

OK (3 tests, 3 assertions)
