--TEST--
TeamCity: assertion failure in setUpBeforeClass() emits beforeFirstTestMethodFailed
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../_files/teamcity-failure-in-set-up-before-class/FailingSetUpBeforeClassTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='1' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\TeamCity\FailingSetUpBeforeClassTest' locationHint='php_qn://%sFailingSetUpBeforeClassTest.php::\PHPUnit\TestFixture\TeamCity\FailingSetUpBeforeClassTest' flowId='%d']
##teamcity[testStarted name='PHPUnit\TestFixture\TeamCity\FailingSetUpBeforeClassTest' flowId='%d']
##teamcity[testFailed name='PHPUnit\TestFixture\TeamCity\FailingSetUpBeforeClassTest' message='Failed asserting that false is true.' details='%s|n' duration='%d' flowId='%d']
##teamcity[testFinished name='PHPUnit\TestFixture\TeamCity\FailingSetUpBeforeClassTest' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\TeamCity\FailingSetUpBeforeClassTest' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\TeamCity\FailingSetUpBeforeClassTest' flowId='%d']
