--TEST--
https://github.com/sebastianbergmann/phpunit/issues/3364
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--teamcity';
$_SERVER['argv'][3] = __DIR__ . '/tests';

require __DIR__ . '/../../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


##teamcity[testCount count='4' flowId='%d']

##teamcity[testSuiteStarted name='tests/end-to-end/regression/GitHub/3364/tests' flowId='%d']

##teamcity[testSuiteStarted name='Issue3364SetupBeforeClassTest' locationHint='php_qn://%s%etests%eend-to-end%eregression%eGitHub%e3364%etests%eIssue3364SetupBeforeClassTest.php::\Issue3364SetupBeforeClassTest' flowId='%d']

##teamcity[testFailed name='Issue3364SetupBeforeClassTest' message='throw exception in setUpBeforeClass' details=' %s%etests%eend-to-end%eregression%eGitHub%e3364%etests%eIssue3364SetupBeforeClassTest.php:18|n ' duration='0' flowId='%d']

##teamcity[testFailed name='Issue3364SetupBeforeClassTest' message='throw exception in setUpBeforeClass' details=' %s%etests%eend-to-end%eregression%eGitHub%e3364%etests%eIssue3364SetupBeforeClassTest.php:18|n ' duration='0' flowId='%d']

##teamcity[testSuiteFinished name='Issue3364SetupBeforeClassTest' flowId='%d']

##teamcity[testSuiteStarted name='Issue3364Test' locationHint='php_qn://%s%etests%eend-to-end%eregression%eGitHub%e3364%etests%eIssue3364SetupTest.php::\Issue3364Test' flowId='%d']

##teamcity[testStarted name='testOneWithSetupException' locationHint='php_qn://%s%etests%eend-to-end%eregression%eGitHub%e3364%etests%eIssue3364SetupTest.php::\Issue3364Test::testOneWithSetupException' flowId='%d']

##teamcity[testFailed name='testOneWithSetupException' message='RuntimeException : throw exception in setUp' details=' %s%etests%eend-to-end%eregression%eGitHub%e3364%etests%eIssue3364SetupTest.php:18|n ' duration='10' flowId='%d']

##teamcity[testFinished name='testOneWithSetupException' duration='10' flowId='%d']

##teamcity[testStarted name='testTwoWithSetupException' locationHint='php_qn://%s%etests%eend-to-end%eregression%eGitHub%e3364%etests%eIssue3364SetupTest.php::\Issue3364Test::testTwoWithSetupException' flowId='%d']

##teamcity[testFailed name='testTwoWithSetupException' message='RuntimeException : throw exception in setUp' details=' %s%etests%eend-to-end%eregression%eGitHub%e3364%etests%eIssue3364SetupTest.php:18|n ' duration='0' flowId='%d']

##teamcity[testFinished name='testTwoWithSetupException' duration='0' flowId='%d']

##teamcity[testSuiteFinished name='Issue3364Test' flowId='%d']

##teamcity[testSuiteFinished name='tests/end-to-end/regression/GitHub/3364/tests' flowId='%d']


Time: %s, Memory: %s


ERRORS!
Tests: 4, Assertions: 0, Errors: 4.
