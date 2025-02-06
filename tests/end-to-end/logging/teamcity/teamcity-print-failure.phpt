--TEST--
TeamCity: print failure message
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] =  __DIR__ . '/../../_files/stop-on-fail-on/FailureTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='%d' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\TestRunnerStopping\FailureTest' locationHint='%sFailureTest.php::\PHPUnit\TestFixture\TestRunnerStopping\FailureTest' flowId='%d']
##teamcity[testStarted name='testOne' locationHint='%sFailureTest.php::\PHPUnit\TestFixture\TestRunnerStopping\FailureTest::testOne' flowId='%d']
##teamcity[testFailed name='testOne' message='Failed asserting that false is true.' details='%sFailureTest.php:18|n' duration='%d' flowId='%d']
##teamcity[testFinished name='testOne' duration='%d' flowId='%d']
##teamcity[testStarted name='testTwo' locationHint='%sFailureTest.php::\PHPUnit\TestFixture\TestRunnerStopping\FailureTest::testTwo' flowId='%d']
##teamcity[testFinished name='testTwo' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\TestRunnerStopping\FailureTest' flowId='%d']
