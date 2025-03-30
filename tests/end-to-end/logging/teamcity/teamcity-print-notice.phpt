--TEST--
TeamCity: print notice message
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--display-notices';
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] =  __DIR__ . '/../../_files/stop-on-fail-on/NoticeTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='2' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\TestRunnerStopping\NoticeTest' locationHint='%sNoticeTest.php::\PHPUnit\TestFixture\TestRunnerStopping\NoticeTest' flowId='%d']
##teamcity[testStarted name='testOne' locationHint='%sNoticeTest.php::\PHPUnit\TestFixture\TestRunnerStopping\NoticeTest::testOne' flowId='%d']
##teamcity[testFinished name='testOne' duration='%d' flowId='%d']
##teamcity[testStarted name='testTwo' locationHint='%sNoticeTest.php::\PHPUnit\TestFixture\TestRunnerStopping\NoticeTest::testTwo' flowId='%d']
##teamcity[testFinished name='testTwo' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\TestRunnerStopping\NoticeTest' flowId='%d']
