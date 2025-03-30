--TEST--
TeamCity: print warning message
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--display-warnings';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] =  __DIR__ . '/../../_files/stop-on-fail-on/WarningTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='2' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\TestRunnerStopping\WarningTest' locationHint='%sWarningTest.php::\PHPUnit\TestFixture\TestRunnerStopping\WarningTest' flowId='%d']
##teamcity[testStarted name='testOne' locationHint='%sWarningTest.php::\PHPUnit\TestFixture\TestRunnerStopping\WarningTest::testOne' flowId='%d']
##teamcity[testFinished name='testOne' duration='%d' flowId='%d']
##teamcity[testStarted name='testTwo' locationHint='%sWarningTest.php::\PHPUnit\TestFixture\TestRunnerStopping\WarningTest::testTwo' flowId='%d']
##teamcity[testFinished name='testTwo' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\TestRunnerStopping\WarningTest' flowId='%d']
