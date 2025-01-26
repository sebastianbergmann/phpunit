--TEST--
TeamCity: print incomplete message
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--display-incomplete';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] =  __DIR__ . '/../../_files/stop-on-fail-on/IncompleteTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='2' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\TestRunnerStopping\IncompleteTest' locationHint='%sIncompleteTest.php::\PHPUnit\TestFixture\TestRunnerStopping\IncompleteTest' flowId='%d']
##teamcity[testStarted name='testOne' locationHint='%sIncompleteTest.php::\PHPUnit\TestFixture\TestRunnerStopping\IncompleteTest::testOne' flowId='%d']
##teamcity[testIgnored name='testOne' message='message' details='%sIncompleteTest.php:18|n' duration='%d' flowId='%d']
##teamcity[testFinished name='testOne' duration='%d' flowId='%d']
##teamcity[testStarted name='testTwo' locationHint='%sIncompleteTest.php::\PHPUnit\TestFixture\TestRunnerStopping\IncompleteTest::testTwo' flowId='%d']
##teamcity[testFinished name='testTwo' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\TestRunnerStopping\IncompleteTest' flowId='%d']
