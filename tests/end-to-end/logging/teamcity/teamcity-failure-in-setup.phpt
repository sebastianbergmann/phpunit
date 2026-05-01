--TEST--
TeamCity: assertion failure in setUp() emits testPreparationFailed
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../_files/teamcity-failure-in-setup/FailingSetUpTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='1' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\TeamCity\FailingSetUpTest' locationHint='php_qn://%sFailingSetUpTest.php::\PHPUnit\TestFixture\TeamCity\FailingSetUpTest' flowId='%d']
##teamcity[testStarted name='testOne' locationHint='php_qn://%sFailingSetUpTest.php::\PHPUnit\TestFixture\TeamCity\FailingSetUpTest::testOne' flowId='%d']
##teamcity[testFailed name='testOne' message='Failed asserting that false is true.' details='%sFailingSetUpTest.php:%d|n' duration='%d' flowId='%d']
##teamcity[testFinished name='testOne' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\TeamCity\FailingSetUpTest' flowId='%d']
