--TEST--
TeamCity: test skipped in test method
--FILE--
<?php declare(strict_types=1);$_SERVER['argv'][] = '--display-skipped';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] =  __DIR__ . '/../../_files/stop-on-fail-on/SkippedTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='2' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\TestRunnerStopping\SkippedTest' locationHint='%sSkippedTest.php::\PHPUnit\TestFixture\TestRunnerStopping\SkippedTest' flowId='%d']
##teamcity[testStarted name='testOne' locationHint='%sSkippedTest.php::\PHPUnit\TestFixture\TestRunnerStopping\SkippedTest::testOne' flowId='%d']
##teamcity[testIgnored name='testOne' message='message' duration='%d' flowId='%d']
##teamcity[testFinished name='testOne' duration='%d' flowId='%d']
##teamcity[testStarted name='testTwo' locationHint='%sSkippedTest.php::\PHPUnit\TestFixture\TestRunnerStopping\SkippedTest::testTwo' flowId='%d']
##teamcity[testFinished name='testTwo' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\TestRunnerStopping\SkippedTest' flowId='%d']
