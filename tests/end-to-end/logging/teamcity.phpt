--TEST--
phpunit --teamcity ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--teamcity';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/BankAccountTest.php');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


##teamcity[testCount count='3' flowId='%d']

##teamcity[testSuiteStarted name='PHPUnit\TestFixture\BankAccountTest' locationHint='php_qn://%s%etests%e_files%eBankAccountTest.php::\PHPUnit\TestFixture\BankAccountTest' flowId='%d']

##teamcity[testStarted name='testBalanceIsInitiallyZero' locationHint='php_qn://%s%etests%e_files%eBankAccountTest.php::\PHPUnit\TestFixture\BankAccountTest::testBalanceIsInitiallyZero' flowId='%d']

##teamcity[testFinished name='testBalanceIsInitiallyZero' duration='%s' flowId='%d']

##teamcity[testStarted name='testBalanceCannotBecomeNegative' locationHint='php_qn://%s%etests%e_files%eBankAccountTest.php::\PHPUnit\TestFixture\BankAccountTest::testBalanceCannotBecomeNegative' flowId='%d']

##teamcity[testFinished name='testBalanceCannotBecomeNegative' duration='%s' flowId='%d']

##teamcity[testStarted name='testBalanceCannotBecomeNegative2' locationHint='php_qn://%s%etests%e_files%eBankAccountTest.php::\PHPUnit\TestFixture\BankAccountTest::testBalanceCannotBecomeNegative2' flowId='%d']

##teamcity[testFinished name='testBalanceCannotBecomeNegative2' duration='%s' flowId='%d']

##teamcity[testSuiteFinished name='PHPUnit\TestFixture\BankAccountTest' flowId='%d']


Time: %s, Memory: %s

OK (3 tests, 3 assertions)
