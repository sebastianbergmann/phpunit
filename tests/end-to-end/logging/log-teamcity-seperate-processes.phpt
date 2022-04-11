--TEST--
phpunit --log-teamcity php://stdout ./_files/TestSeperateProcesses.php
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = \realpath(__DIR__ . '/_files/TestSeperateProcesses.php');

require __DIR__ . '/../../bootstrap.php';

PHPUnit\TextUI\Application::main();
--EXPECTF--
PHPUnit %s #StandWithUkraine


##teamcity[testCount count='1' flowId='%d']

##teamcity[testSuiteStarted name='TestSeperateProcesses' locationHint='%s' flowId='%d']

##teamcity[testStarted name='testStdout' locationHint='%s' flowId='%d']
F                                                                   1 / 1 (100%)
##teamcity[testFailed name='testStdout' message='Failed asserting that false is true.' details='%s' duration='%d' flowId='%d']
setUp output;test output;tearDown output;setUp output;test output;tearDown output;
##teamcity[testFinished name='testStdout' duration='%d' flowId='%d']

##teamcity[testSuiteFinished name='TestSeperateProcesses' flowId='%d']


Time: %s, Memory: %s

There was 1 failure:

1) TestSeperateProcesses::testStdout
Failed asserting that false is true.

%s

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
