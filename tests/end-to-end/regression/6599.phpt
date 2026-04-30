--TEST--
GH-6599: --teamcity does not wrap setUp() failures with testStarted/testFinished
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/6599/Issue6599Test.php';

require_once __DIR__ . '/../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='3' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\Issue6599\Issue6599Test' locationHint='php_qn://%sIssue6599Test.php::\PHPUnit\TestFixture\Issue6599\Issue6599Test' flowId='%d']
##teamcity[testSuiteStarted name='testWithDataProvider' locationHint='php_qn://%sIssue6599Test.php::\PHPUnit\TestFixture\Issue6599\Issue6599Test::testWithDataProvider' flowId='%d']
##teamcity[testStarted name='testWithDataProvider with data set "one"' locationHint='php_qn://%sIssue6599Test.php::\PHPUnit\TestFixture\Issue6599\Issue6599Test::testWithDataProvider with data set "one"' flowId='%d']
##teamcity[testFailed name='testWithDataProvider with data set "one"' message='RuntimeException: failure in setUp' details='%sIssue6599Test.php:%d|n' duration='%d' flowId='%d']
##teamcity[testFinished name='testWithDataProvider with data set "one"' duration='%d' flowId='%d']
##teamcity[testStarted name='testWithDataProvider with data set "two"' locationHint='php_qn://%sIssue6599Test.php::\PHPUnit\TestFixture\Issue6599\Issue6599Test::testWithDataProvider with data set "two"' flowId='%d']
##teamcity[testFailed name='testWithDataProvider with data set "two"' message='RuntimeException: failure in setUp' details='%sIssue6599Test.php:%d|n' duration='%d' flowId='%d']
##teamcity[testFinished name='testWithDataProvider with data set "two"' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='testWithDataProvider' flowId='%d']
##teamcity[testStarted name='testWithoutDataProvider' locationHint='php_qn://%sIssue6599Test.php::\PHPUnit\TestFixture\Issue6599\Issue6599Test::testWithoutDataProvider' flowId='%d']
##teamcity[testFailed name='testWithoutDataProvider' message='RuntimeException: failure in setUp' details='%sIssue6599Test.php:%d|n' duration='%d' flowId='%d']
##teamcity[testFinished name='testWithoutDataProvider' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\Issue6599\Issue6599Test' flowId='%d']
