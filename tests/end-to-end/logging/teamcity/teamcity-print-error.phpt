--TEST--
TeamCity: print error message
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--display-errors';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] =  __DIR__ . '/../../_files/stop-on-fail-on/ErrorTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='2' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\TestRunnerStopping\ErrorTest' locationHint='%sErrorTest.php::\PHPUnit\TestFixture\TestRunnerStopping\ErrorTest' flowId='%d']
##teamcity[testStarted name='testOne' locationHint='%sErrorTest.php::\PHPUnit\TestFixture\TestRunnerStopping\ErrorTest::testOne' flowId='%d']
##teamcity[testFailed name='testOne' message='Exception: message' details='%sErrorTest.php:19|n' duration='%d' flowId='%d']
##teamcity[testFinished name='testOne' duration='%d' flowId='%d']
##teamcity[testStarted name='testTwo' locationHint='%sErrorTest.php::\PHPUnit\TestFixture\TestRunnerStopping\ErrorTest::testTwo' flowId='%d']
##teamcity[testFinished name='testTwo' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\TestRunnerStopping\ErrorTest' flowId='%d']
