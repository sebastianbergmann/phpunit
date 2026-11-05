--TEST--
TeamCity logging includes how a property was falsified, with its minimal counterexample, in the details of the failure
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-record-test-run-history';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--random-seed';
$_SERVER['argv'][] = '1234';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../../property-based-testing/_files/FalsifiedPropertyTest.php';

require __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='1' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest' locationHint='php_qn:%sFalsifiedPropertyTest.php::\PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest::testSumIsAtMostTen' locationHint='php_qn:%sFalsifiedPropertyTest.php::\PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest::testSumIsAtMostTen' flowId='%d']
##teamcity[testStarted name='testSumIsAtMostTen' locationHint='php_qn:%sFalsifiedPropertyTest.php::\PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest::testSumIsAtMostTen' flowId='%d']
##teamcity[testFailed name='testSumIsAtMostTen' message='Failed asserting that 11 is equal to 10 or is less than 10.' details='Property falsified after 1 example, counterexample shrunk in 2 steps:|n|n    $a = 0;|n    $b = 11;|n|n%sFalsifiedPropertyTest.php:%d|n' duration='%d' flowId='%d']
##teamcity[testFinished name='testSumIsAtMostTen' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest::testSumIsAtMostTen' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\PropertyBasedTesting\FalsifiedPropertyTest' flowId='%d']
