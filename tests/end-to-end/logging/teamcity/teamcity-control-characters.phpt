--TEST--
TeamCity: control characters in user-supplied strings are made visible
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../../_files/ControlCharactersTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='%d' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\ControlCharactersTest' locationHint='php_qn://%sControlCharactersTest.php::\PHPUnit\TestFixture\ControlCharactersTest' flowId='%d']
##teamcity[testStarted name='testUnexpectedOutput' locationHint='php_qn://%sControlCharactersTest.php::\PHPUnit\TestFixture\ControlCharactersTest::testUnexpectedOutput' flowId='%d']
##teamcity[testFinished name='testUnexpectedOutput' duration='%d' flowId='%d']
##teamcity[testStarted name='testFailureMessage' locationHint='php_qn://%sControlCharactersTest.php::\PHPUnit\TestFixture\ControlCharactersTest::testFailureMessage' flowId='%d']
##teamcity[testFailed name='testFailureMessage' message='Failed\u{001B}|[2K\u{000D}Everything is fine' details='%s:%d|n' duration='%d' flowId='%d']
##teamcity[testFinished name='testFailureMessage' duration='%d' flowId='%d']
##teamcity[testSuiteStarted name='testDataSetName' locationHint='php_qn://%sControlCharactersTest.php::\PHPUnit\TestFixture\ControlCharactersTest::testDataSetName' flowId='%d']
##teamcity[testStarted name='testDataSetName with data set "\u{001B}|[31mdata set name\u{001B}|[0m"' locationHint='php_qn://%sControlCharactersTest.php::\PHPUnit\TestFixture\ControlCharactersTest::testDataSetName with data set "\u{001B}|[31mdata set name\u{001B}|[0m"' flowId='%d']
##teamcity[testFailed name='testDataSetName with data set "\u{001B}|[31mdata set name\u{001B}|[0m"' message='Failed asserting that true is false.' details='%s:%d|n' duration='%d' flowId='%d']
##teamcity[testFinished name='testDataSetName with data set "\u{001B}|[31mdata set name\u{001B}|[0m"' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='testDataSetName' flowId='%d']
##teamcity[testStarted name='testTestDoxLabel' locationHint='php_qn://%sControlCharactersTest.php::\PHPUnit\TestFixture\ControlCharactersTest::testTestDoxLabel' flowId='%d']
##teamcity[testIgnored name='testTestDoxLabel' message='Skipped\u{001B}|[2K\u{000D}Nothing to see here' duration='%d' flowId='%d']
##teamcity[testFinished name='testTestDoxLabel' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\ControlCharactersTest' flowId='%d']
