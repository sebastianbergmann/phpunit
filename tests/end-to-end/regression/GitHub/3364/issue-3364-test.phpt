--TEST--
https://github.com/sebastianbergmann/phpunit/issues/3364
--FILE--
<?php
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--teamcity';
$_SERVER['argv'][3] = __DIR__ . DIRECTORY_SEPARATOR . 'tests';

require __DIR__ . '/../../../../bootstrap.php';
PHPUnit\TextUI\Command::main();
--EXPECTF--
PHPUnit %s by Sebastian Bergmann and contributors.


##teamcity[testCount count='4' flowId='%d']

##teamcity[testSuiteStarted name='%stests%eend-to-end%eregression%eGitHub%e3364%etests' flowId='%d']

##teamcity[testSuiteStarted name='Issue3364SetupBeforeClassTest' locationHint='php_qn://%s%etests%eend-to-end%eregression%eGitHub%e3364%etests%eIssue3364SetupBeforeClassTest.php::\Issue3364SetupBeforeClassTest' flowId='%d']

##teamcity[testStarted name='testOneWithClassSetupException' locationHint='php_qn://%s%etests%eend-to-end%eregression%eGitHub%e3364%etests%eIssue3364SetupBeforeClassTest.php::\Issue3364SetupBeforeClassTest::testOneWithClassSetupException' flowId='%d']

##teamcity[testFailed name='testOneWithClassSetupException' message='throw exception in setUpBeforeClass' details=' %s%etests%eend-to-end%eregression%eGitHub%e3364%etests%eIssue3364SetupBeforeClassTest.php:18|n ' duration='%d' flowId='%d']

##teamcity[testFinished name='testOneWithClassSetupException' duration='%d' flowId='%d']

##teamcity[testStarted name='testTwoWithClassSetupException' locationHint='php_qn://%s%etests%eend-to-end%eregression%eGitHub%e3364%etests%eIssue3364SetupBeforeClassTest.php::\Issue3364SetupBeforeClassTest::testTwoWithClassSetupException' flowId='%d']

##teamcity[testFailed name='testTwoWithClassSetupException' message='throw exception in setUpBeforeClass' details=' %s%etests%eend-to-end%eregression%eGitHub%e3364%etests%eIssue3364SetupBeforeClassTest.php:18|n ' duration='%d' flowId='%d']

##teamcity[testFinished name='testTwoWithClassSetupException' duration='%d' flowId='%d']

##teamcity[testSuiteFinished name='Issue3364SetupBeforeClassTest' flowId='%d']

##teamcity[testSuiteStarted name='Issue3364SetupTest' locationHint='php_qn://%s%etests%eend-to-end%eregression%eGitHub%e3364%etests%eIssue3364SetupTest.php::\Issue3364SetupTest' flowId='%d']

##teamcity[testStarted name='testOneWithSetupException' locationHint='php_qn://%s%etests%eend-to-end%eregression%eGitHub%e3364%etests%eIssue3364SetupTest.php::\Issue3364SetupTest::testOneWithSetupException' flowId='%d']

##teamcity[testFailed name='testOneWithSetupException' message='RuntimeException : throw exception in setUp' details=' %s%etests%eend-to-end%eregression%eGitHub%e3364%etests%eIssue3364SetupTest.php:18|n ' duration='%d' flowId='%d']

##teamcity[testFinished name='testOneWithSetupException' duration='%d' flowId='%d']

##teamcity[testStarted name='testTwoWithSetupException' locationHint='php_qn://%s%etests%eend-to-end%eregression%eGitHub%e3364%etests%eIssue3364SetupTest.php::\Issue3364SetupTest::testTwoWithSetupException' flowId='%d']

##teamcity[testFailed name='testTwoWithSetupException' message='RuntimeException : throw exception in setUp' details=' %s%etests%eend-to-end%eregression%eGitHub%e3364%etests%eIssue3364SetupTest.php:18|n ' duration='%d' flowId='%d']

##teamcity[testFinished name='testTwoWithSetupException' duration='%d' flowId='%d']

##teamcity[testSuiteFinished name='Issue3364SetupTest' flowId='%d']

##teamcity[testSuiteFinished name='%stests%eend-to-end%eregression%eGitHub%e3364%etests' flowId='%d']


Time: %s, Memory: %s


ERRORS!
Tests: 4, Assertions: 0, Errors: 4.
