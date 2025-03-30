--TEST--
TeamCity: print risky message
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] =  __DIR__ . '/../../_files/stop-on-fail-on/RiskyTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='2' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\TestRunnerStopping\RiskyTest' locationHint='%sRiskyTest.php::\PHPUnit\TestFixture\TestRunnerStopping\RiskyTest' flowId='%d']
##teamcity[testStarted name='testOne' locationHint='%sRiskyTest.php::\PHPUnit\TestFixture\TestRunnerStopping\RiskyTest::testOne' flowId='%d']
##teamcity[testFailed name='testOne' message='This test did not perform any assertions' details='' duration='%d' flowId='%d']
##teamcity[testFinished name='testOne' duration='%d' flowId='%d']
##teamcity[testStarted name='testTwo' locationHint='%sRiskyTest.php::\PHPUnit\TestFixture\TestRunnerStopping\RiskyTest::testTwo' flowId='%d']
##teamcity[testFinished name='testTwo' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\TestRunnerStopping\RiskyTest' flowId='%d']
