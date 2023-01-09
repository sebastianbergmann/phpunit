--TEST--
phpunit --log-teamcity php://stdout ../../_files/BankAccountTest.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = \realpath(__DIR__ . '/../../_files/ComparisonFailureTest.php');

require_once __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


##teamcity[testCount count='1' flowId='%d']

##teamcity[testSuiteStarted name='PHPUnit\TestFixture\ComparisonFailureTest' locationHint='php_qn:%sComparisonFailureTest.php::\PHPUnit\TestFixture\ComparisonFailureTest' flowId='%d']

##teamcity[testStarted name='testOne' locationHint='php_qn:%sComparisonFailureTest.php::\PHPUnit\TestFixture\ComparisonFailureTest::testOne' flowId='%d']
F                                                                   1 / 1 (100%)
##teamcity[testFailed name='testOne' message='Failed asserting that false matches expected true.' details=' %sComparisonFailureTest.php:%d|n ' duration='%s' type='comparisonFailure' actual='false' expected='true' flowId='%d']

##teamcity[testFinished name='testOne' duration='%s' flowId='%d']

##teamcity[testSuiteFinished name='PHPUnit\TestFixture\ComparisonFailureTest' flowId='%d']


Time: %s, Memory: %s MB

There was 1 failure:

1) PHPUnit\TestFixture\ComparisonFailureTest::testOne
Failed asserting that false matches expected true.

%sComparisonFailureTest.php:%d

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
