--TEST--
TeamCity: invalid dependency emits testErrored without prior testStarted
--FILE--
<?php declare(strict_types=1);
$_SERVER['argv'][] = '--do-not-cache-result';
$_SERVER['argv'][] = '--no-configuration';
$_SERVER['argv'][] = '--no-output';
$_SERVER['argv'][] = '--log-teamcity';
$_SERVER['argv'][] = 'php://stdout';
$_SERVER['argv'][] = __DIR__ . '/../_files/teamcity-invalid-dependency/InvalidDependencyTest.php';

require_once __DIR__ . '/../../../bootstrap.php';

(new PHPUnit\TextUI\Application)->run($_SERVER['argv']);
--EXPECTF--
##teamcity[testCount count='1' flowId='%d']
##teamcity[testSuiteStarted name='PHPUnit\TestFixture\TeamCity\InvalidDependencyTest' locationHint='php_qn://%sInvalidDependencyTest.php::\PHPUnit\TestFixture\TeamCity\InvalidDependencyTest' flowId='%d']
##teamcity[testFailed name='testOne' message='This test depends on "PHPUnit\TestFixture\TeamCity\InvalidDependencyTest::doesNotExist" which does not exist' details='' duration='%d' flowId='%d']
##teamcity[testSuiteFinished name='PHPUnit\TestFixture\TeamCity\InvalidDependencyTest' flowId='%d']
