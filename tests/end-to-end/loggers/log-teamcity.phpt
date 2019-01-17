--TEST--
phpunit --log-teamcity php://stdout BankAccountTest ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$arguments = [
    '--no-configuration',
    '--log-teamcity',
    'php://stdout',
    'BankAccountTest',
    \realpath(__DIR__ . '/../../_files/BankAccountTest.php'),
];
\array_splice($_SERVER['argv'], 1, count($arguments), $arguments);

require __DIR__ . '/../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


##teamcity[testCount count='3' flowId='%d']

##teamcity[testSuiteStarted name='BankAccountTest' locationHint='php_qn://%s%etests%e_files%eBankAccountTest.php::\BankAccountTest' flowId='%d']

##teamcity[testStarted name='testBalanceIsInitiallyZero' locationHint='php_qn://%s%etests%e_files%eBankAccountTest.php::\BankAccountTest::testBalanceIsInitiallyZero' flowId='%d']
.
##teamcity[testFinished name='testBalanceIsInitiallyZero' duration='%s' flowId='%d']

##teamcity[testStarted name='testBalanceCannotBecomeNegative' locationHint='php_qn://%s%etests%e_files%eBankAccountTest.php::\BankAccountTest::testBalanceCannotBecomeNegative' flowId='%d']
.
##teamcity[testFinished name='testBalanceCannotBecomeNegative' duration='%s' flowId='%d']

##teamcity[testStarted name='testBalanceCannotBecomeNegative2' locationHint='php_qn://%s%etests%e_files%eBankAccountTest.php::\BankAccountTest::testBalanceCannotBecomeNegative2' flowId='%d']
.                                                                 3 / 3 (100%)
##teamcity[testFinished name='testBalanceCannotBecomeNegative2' duration='%s' flowId='%d']

##teamcity[testSuiteFinished name='BankAccountTest' flowId='%d']


Time: %s, Memory: %s

OK (3 tests, 3 assertions)
