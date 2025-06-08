--TEST--
TeamCity: test skipped in before-class method
--FILE--
<?php declare(strict_types=1);$_SERVER['argv'][] = '--display-skipped';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] =  __DIR__ . '/../../_files/stop-on-fail-on/SkippedBeforeClassTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='1' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\TestRunnerStopping\SkippedBeforeClassTest' locationHint='php_qn:%sSkippedBeforeClassTest.php::\PHPUnit\TestFixture\TestRunnerStopping\SkippedBeforeClassTest' flowId='%d']
##teamcity[testIgnored name='PHPUnit\TestFixture\TestRunnerStopping\SkippedBeforeClassTest' message='message' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\TestRunnerStopping\SkippedBeforeClassTest' message='message' duration='%d' flowId='%d']
