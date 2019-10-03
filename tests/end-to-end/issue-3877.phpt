--TEST--
phpunit --list-groups BankAccountTest ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][1] = '--configuration';
$_SERVER['argv'][2] =  __DIR__ . '/../_files/Issue3877/phpunit.xml';

require __DIR__ . '/../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.

string(47) "EchoWhenStartTestsuiteListener::startTestSuite:"
string(53) "EchoWhenStartTestsuiteListener::startTestSuite:Suite1"
.string(51) "EchoWhenStartTestsuiteListener::endTestSuite:Suite1"
string(53) "EchoWhenStartTestsuiteListener::startTestSuite:Suite2"
.                                                                  2 / 2 (100%)string(51) "EchoWhenStartTestsuiteListener::endTestSuite:Suite2"
string(45) "EchoWhenStartTestsuiteListener::endTestSuite:"

Time: %s, Memory: %s

OK (2 tests, 2 assertions)
