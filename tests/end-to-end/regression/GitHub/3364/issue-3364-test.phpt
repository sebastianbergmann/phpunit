--TEST--
https://github.com/sebastianbergmann/phpunit/issues/3364
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--teamcity';
$_SERVER['argv'][3] = __DIR__ . '/Issue3364Test.php';

require __DIR__ . '/../../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


##teamcity[testCount count='1' flowId='%d']

##teamcity[testSuiteStarted name='Issue3364Test' locationHint='php_qn://%s%etests%eend-to-end%eregression%eGitHub%e3364%eIssue3364Test.php::\Issue3364Test' flowId='%d']

##teamcity[testStarted name='testSomething' locationHint='php_qn://%s%etests%eend-to-end%eregression%eGitHub%e3364%eIssue3364Test.php::\Issue3364Test::testSomething' flowId='%d']

##teamcity[testFailed name='testSomething' message='RuntimeException : Something|'s not quite right!' details=' %s%etests%eend-to-end%eregression%eGitHub%e3364%eIssue3364Test.php:17|n%s' duration='%d' flowId='%d']

##teamcity[testFinished name='testSomething' duration='%d' flowId='%d']

##teamcity[testSuiteFinished name='Issue3364Test' flowId='%d']


Time: %s, Memory: %s


ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
